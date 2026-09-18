"""Pruebas integrales locales. Exigen APP_ENV=local; crean cuentas desechables propias."""
import argparse, html, json, re, subprocess, sys, uuid
from pathlib import Path
from urllib.request import build_opener, Request, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.error import HTTPError
from urllib.parse import urlencode, urlsplit
from http.cookiejar import CookieJar
from html.parser import HTMLParser

parser=argparse.ArgumentParser()
parser.add_argument('--base-url',default='http://127.0.0.1:8097')
parser.add_argument('--project-root',type=Path,default=Path(__file__).resolve().parents[1])
parser.add_argument('--artifact-root',type=Path)
parser.add_argument('--php',default='php')
parser.add_argument('--node',default='node')
args=parser.parse_args()
if urlsplit(args.base_url).hostname not in ('localhost','127.0.0.1'): raise SystemExit('Solo servidor local')
root=args.project_root.resolve()
out=args.artifact_root or root/'.local/test-results'
out.mkdir(parents=True,exist_ok=True)
results=[]

def db(sql=None,params=None,action=None):
    payload={'action':action} if action else {'sql':sql,'params':params or []}
    result=subprocess.run([args.php,'-d','xdebug.mode=off',str(Path(__file__).with_name('db-fixtures.php')),str(root)],input=json.dumps(payload),text=True,capture_output=True)
    if result.returncode: raise RuntimeError(result.stderr)
    return json.loads(result.stdout)

def rows(sql,params=None): return db(sql,params)['rows']
def scalar(sql,params=None): return next(iter(rows(sql,params)[0].values()))
def check(name,condition):
    results.append({'name':name,'passed':bool(condition)})
    print(('PASS ' if condition else 'FAIL ')+name,flush=True)
    if not condition: raise AssertionError(name)

class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs): return None

class Client:
    def __init__(self): self.opener=build_opener(HTTPCookieProcessor(CookieJar()),NoRedirect()); self.csrf=None
    def request(self,path,data=None,ajax=False):
        headers={'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} if ajax else {}
        req=Request(args.base_url+path,data=urlencode(data,doseq=True).encode() if data is not None else None,headers=headers)
        try: res=self.opener.open(req,timeout=20)
        except HTTPError as err: res=err
        body=res.read().decode('utf-8'); return res.code,body,dict(res.headers)
    def json(self,path,data=None):
        code,body,_=self.request(path,data,True)
        try: payload=json.loads(body)
        except Exception: raise AssertionError(f'{path}: HTTP {code}, respuesta no JSON: {body[:400]}')
        return code,payload
    def token(self):
        code,data=self.json('/sesion/csrf'); assert code==200; self.csrf=data['csrf_token']; return self.csrf
    def post(self,path,data=None,csrf=True):
        payload=dict(data or {})
        if csrf: payload['csrf_token']=self.csrf
        return self.json(path,payload)

def login(client,user,password,role=None):
    code,body,_=client.request('/login')
    token=re.search(r'name="csrf_token" value="([^"]+)"',body).group(1)
    payload={'csrf_token':token,'correo':user['correo'],'contrasena':password}
    if role is not None: payload['rol']=role
    code,body,headers=client.request('/login/ingresar',payload)
    if code==302: client.token()
    return code,body,headers

class Scripts(HTMLParser):
    def __init__(self): super().__init__(); self.scripts=[]; self.handlers=[]; self.inside=False
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if tag=='script' and 'src' not in attrs: self.inside=True; self.scripts.append('')
        for key,value in attrs.items():
            if key.startswith('on') and value: self.handlers.append(value)
    def handle_data(self,data):
        if self.inside: self.scripts[-1]+=data
    def handle_endtag(self,tag):
        if tag=='script': self.inside=False

def verify_js(name,body):
    parsed=Scripts(); parsed.feed(body)
    source='\n'.join(parsed.scripts)+'\n'+'\n'.join('function handler_'+str(i)+'(){'+v+'}' for i,v in enumerate(parsed.handlers))
    path=out/(name+'.js'); path.write_text(source,encoding='utf-8')
    result=subprocess.run([args.node,'--check',str(path)],capture_output=True,text=True)
    check('JavaScript y botones: '+name,result.returncode==0)

fixture=db(action='init'); users=fixture['users']; password=fixture['password']; restore=[]; pending_module=None; pending_raps=[]
try:
    check('Contenido existente conserva publicación válida',db(action='validate-course')['failures']==[])
    guest=Client()
    check('Home y login públicos',guest.request('/')[0]==200 and guest.request('/login')[0]==200)
    for path in ['/database/seeds/run_seeds.php','/database/migrar.php','/.env','/config/credenciales.php','/tests/db-fixtures.php']:
        check('Archivos internos bloqueados: '+path,guest.request(path)[0]==403)
    check('CSS accesible',guest.request('/assets/css/estilos.css')[0]==200)
    check('AJAX sin sesión devuelve 401',guest.json('/aprendiz/rap/guardar-progreso',{})[0]==401)
    wrong=Client(); code,body,_=login(wrong,users['aprendiz'],password,'admin')
    check('Rol enviado por cliente no eleva privilegios',code==302 and wrong.request('/admin')[0] in (302,403))
    login_page=guest.request('/login')[1]
    check('Login sin selector ni campo de rol','tab-aprendiz' not in login_page and 'tab-instructor' not in login_page and 'tab-admin' not in login_page and 'name="rol"' not in login_page)
    invalid=Client(); code,body,_=login(invalid,users['aprendiz'],password+'incorrecta')
    check('Login rechaza contraseña incorrecta',code==200 and invalid.request('/admin')[0] in (302,403))
    clients={}
    for role in ['aprendiz','instructor','admin']:
        client=Client(); clients[role]=client
        code,_,headers=login(client,users[role],password)
        check('Login automático '+role,code==302 and headers.get('Location')==('/' if role=='aprendiz' else '/'+role))
        allowed='/' if role=='aprendiz' else '/'+role
        check('Panel '+role,client.request(allowed)[0]==200)
        for forbidden in ['/admin','/instructor']:
            if forbidden!=allowed: check(role+' no accede a '+forbidden,client.request(forbidden)[0] in (302,403))
    admin=clients['admin']; learner=clients['aprendiz']; instructor=clients['instructor']
    for role,client,field,value in [('instructor',instructor,'activo',0),('admin',admin,'rol','aprendiz')]:
        user=users[role]
        db(f'UPDATE usuarios SET {field}=? WHERE id=?',[value,user['id']])
        check('Sesión revocada tras cambiar '+field,client.request('/'+role)[0]==302)
        db(f'UPDATE usuarios SET {field}=? WHERE id=?',[1 if field=='activo' else role,user['id']])
        login(client,user,password)
    other=Client(); login(other,users['aprendiz'],password)
    db('UPDATE usuarios SET eliminado=1 WHERE id=?',[users['aprendiz']['id']])
    check('Sesión de cuenta eliminada revocada',other.json('/aprendiz/leaderboard')[0]==401)
    db('UPDATE usuarios SET eliminado=0 WHERE id=?',[users['aprendiz']['id']])
    temp=Client(); code,_,headers=login(temp,users['temporal'],password)
    check('Clave temporal lleva a cambiar clave',code==302 and headers.get('Location')=='/cambiar-clave')
    check('No se evita cambio por URL',temp.request('/instructor')[2].get('Location')=='/cambiar-clave')
    code,data=temp.post('/aprendiz/rap/guardar-progreso',{'rap_id':'x','porcentaje':75})
    check('Cambio de clave obligatorio también en AJAX',code==403 and data.get('redirigir')=='/cambiar-clave')
    code,_,_=temp.request('/cambiar-clave/guardar',{'csrf_token':temp.csrf,'contrasena':'TemporalNueva7','contrasena_confirmar':'TemporalNueva7'})
    check('Cambio de clave conserva la sesión propia',code==302 and temp.request('/instructor')[0]==200)
    raps=rows('SELECT r.id,r.nivel_id,n.orden FROM rap r JOIN nivel n ON n.id=r.nivel_id WHERE r.activo=1 ORDER BY n.orden,r.orden')
    first=raps[0]; rid=first['id']; nid=first['nivel_id']
    check('RAP posterior bloqueado por URL',learner.request('/aprendiz/rap?id='+raps[1]['id'])[0]==403)
    check('RAP posterior bloqueado por POST',learner.post('/aprendiz/rap/guardar-progreso',{'rap_id':raps[1]['id'],'porcentaje':75})[0]==403)
    for endpoint in ['guardar-progreso','guardar-ejercicio','guardar-quiz','marcar-vocabulario','iniciar-quiz']:
        check('CSRF obligatorio: '+endpoint,learner.post('/aprendiz/rap/'+endpoint,{},False)[0]==419)
    code,page,_=learner.request('/aprendiz/rap?id='+rid)
    check('RAP inicial disponible',code==200)
    verify_js('rap-inicial',page)
    # El texto clínico admite contracciones, comillas y etiquetas literales.
    quote_ex=str(uuid.uuid4()); quote_option=str(uuid.uuid4()); quote_question=str(uuid.uuid4())
    existing_quiz=scalar('SELECT id FROM quiz WHERE rap_id=? LIMIT 1',[rid])
    db('INSERT INTO ejercicio (id,rap_id,tipo,enunciado,activo) VALUES (?,?,?,?,1)',[quote_ex,rid,'completar_frase','___ a nurse <b>literal</b>'])
    db('INSERT INTO ejercicio_opcion (id,ejercicio_id,texto,es_correcta) VALUES (?,?,?,1)',[quote_option,quote_ex,'I\'m "the nurse"'])
    db('INSERT INTO pregunta (id,quiz_id,texto,opciones,respuesta_correcta,activo) VALUES (?,?,?,?,?,1)',[quote_question,existing_quiz,'Quotation test',json.dumps(['I\'m "the nurse"','Other']), 'I\'m "the nurse"'])
    try:
        code,quoted,_=admin.request('/aprendiz/rap?id='+rid)
        verify_js('contenido-con-comillas',quoted)
        check('Enunciado no ejecuta HTML','<b>literal</b>' not in quoted and 'a nurse literal' in quoted)
    finally:
        db('DELETE FROM pregunta WHERE id=?',[quote_question])
        db('DELETE FROM ejercicio_opcion WHERE id=?',[quote_option])
        db('DELETE FROM ejercicio WHERE id=?',[quote_ex])
    practice=json.loads(re.search(r'const practicaId = (.*?);',page).group(1))
    common={'rap_id':rid,'practica_id':practice}
    check('No se puede saltar a 75%',learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':75})[0]==403)
    old_token=learner.csrf
    learner.request('/logout')
    check('Operación pendiente sin sesión recibe 401',learner.json('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':25,'csrf_token':old_token})[0]==401)
    login(learner,users['aprendiz'],password)
    check('Token antiguo se rechaza tras nuevo login',learner.json('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':25,'csrf_token':old_token})[0]==419)
    other_learner=Client(); login(other_learner,users['otroaprendiz'],password)
    check('Práctica de otra cuenta se rechaza',other_learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':75})[0]==403)
    voc=rows('SELECT v.id FROM vocabulario v JOIN rap r ON r.id=v.rap_id WHERE r.nivel_id=? AND r.activo=1 AND v.activo=1 ORDER BY v.rap_id,v.id LIMIT 3',[nid])
    pairs=[{'en':v['id'],'es':v['id']} for v in voc]
    check('Warm-Up verificable',learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':25,'pares':json.dumps(pairs)})[1].get('exito'))
    check('Absorption sigue a Warm-Up',learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':50})[1].get('exito'))
    check('Práctica incompleta no abre quiz',learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':75})[0]==403)
    exercises=rows('SELECT e.* FROM ejercicio e JOIN rap r ON r.id=e.rap_id WHERE r.nivel_id=? AND r.activo=1 AND e.activo=1 ORDER BY e.rap_id,e.id',[nid])
    target=exercises[0]
    wrong_data={'ejercicio_id':target['id'],'practica_id':practice,'solicitud_id':str(uuid.uuid4()),'es_correcto':1,'respuesta':''}
    code,data=learner.post('/aprendiz/rap/guardar-ejercicio',wrong_data)
    check('Cliente no puede declarar acierto vacío',code==200 and data['es_correcto'] is False and data['xp_ganados']==0)
    total_xp=0
    for ex in exercises:
        opts=rows('SELECT id,texto,es_correcta FROM ejercicio_opcion WHERE ejercicio_id=? ORDER BY id',[ex['id']])
        payload={'ejercicio_id':ex['id'],'practica_id':practice,'solicitud_id':str(uuid.uuid4()),'tiempo_respuesta_ms':1000}
        if ex['tipo'] in ('seleccion_multiple','role_play'):
            option=next(o for o in opts if o['es_correcta']); payload['opcion_id']=option['id']; payload['respuesta']=option['texto']
        elif ex['tipo']=='arrastrar_soltar':
            payload['respuesta']=json.dumps([dict(zip(('en','es'),o['texto'].split('=',1))) for o in opts])
        elif ex['tipo']=='ordenar_dialogo': payload['respuesta']=' | '.join(o['texto'] for o in opts)
        elif ex['tipo']=='escucha_escribe': payload['respuesta']=opts[0]['texto']
        else: payload['respuesta']=next(o['texto'] for o in opts if o['es_correcta'])
        code,data=learner.post('/aprendiz/rap/guardar-ejercicio',payload)
        check('Servidor califica '+ex['tipo'],code==200 and data.get('es_correcto') is True)
        total_xp+=data['xp_ganados']
        before=scalar('SELECT xp_puntos FROM usuarios WHERE id=?',[users['aprendiz']['id']])
        check('Reintento HTTP es idempotente '+ex['tipo'],learner.post('/aprendiz/rap/guardar-ejercicio',payload)[1]==data and scalar('SELECT xp_puntos FROM usuarios WHERE id=?',[users['aprendiz']['id']])==before)
    check('XP de ejercicios persiste',total_xp>0 and scalar('SELECT xp_puntos FROM usuarios WHERE id=?',[users['aprendiz']['id']])==total_xp)
    target=exercises[0]
    count=scalar('SELECT COUNT(*) FROM intento_ejercicio WHERE usuario_id=? AND ejercicio_id=?',[users['aprendiz']['id'],target['id']])
    for _ in range(max(0,target['max_intentos']-count)):
        learner.post('/aprendiz/rap/guardar-ejercicio',{**wrong_data,'solicitud_id':str(uuid.uuid4())})
    check('Límite de intentos de ejercicio se aplica',learner.post('/aprendiz/rap/guardar-ejercicio',{**wrong_data,'solicitud_id':str(uuid.uuid4())})[0]==403)
    check('Práctica realizada abre quiz',learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':75})[1].get('exito'))
    check('No se envía quiz sin comenzar',learner.post('/aprendiz/rap/guardar-quiz',common)[0]==403)
    questions=rows('SELECT p.* FROM pregunta p JOIN quiz q ON q.id=p.quiz_id JOIN rap r ON r.id=q.rap_id WHERE r.nivel_id=? AND r.activo=1 AND q.activo=1 AND p.activo=1',[nid])
    start={**common,'preguntas_ids':json.dumps([p['id'] for p in questions])}
    code,data=learner.post('/aprendiz/rap/iniciar-quiz',start)
    check('Quiz obtiene inicio del servidor',code==200 and data.get('sesion_quiz_id'))
    quiz_session=data['sesion_quiz_id']
    check('Repetir inicio no renueva el reloj',learner.post('/aprendiz/rap/iniciar-quiz',start)[1]['sesion_quiz_id']==quiz_session)
    code,failed=learner.post('/aprendiz/rap/guardar-quiz',{**common,'sesion_quiz_id':quiz_session,'duracion_seg':-999})
    check('Quiz fallido consume un intento',code==200 and not failed['aprobado'] and failed['intentos']['usados']==1)
    marker=scalar('SELECT ronda_quiz_desde FROM progreso WHERE usuario_id=? AND rap_id=?',[users['aprendiz']['id'],rid])
    learner.post('/aprendiz/rap/guardar-progreso',{**common,'porcentaje':75})
    check('Repetir 75% no reinicia la ronda',scalar('SELECT ronda_quiz_desde FROM progreso WHERE usuario_id=? AND rap_id=?',[users['aprendiz']['id'],rid])==marker)
    code,data=learner.post('/aprendiz/rap/iniciar-quiz',start); quiz_session=data['sesion_quiz_id']
    db('UPDATE sesion_quiz SET vence_en=DATE_SUB(NOW(),INTERVAL 10 SECOND) WHERE id=?',[quiz_session])
    check('Vencimiento se verifica en servidor',learner.post('/aprendiz/rap/guardar-quiz',{**common,'sesion_quiz_id':quiz_session})[0]==403)
    code,data=learner.post('/aprendiz/rap/iniciar-quiz',start); quiz_session=data['sesion_quiz_id']
    answers={f"respuestas[{p['id']}]":p['respuesta_correcta'] for p in questions}
    code,passed=learner.post('/aprendiz/rap/guardar-quiz',{**common,**answers,'sesion_quiz_id':quiz_session,'duracion_seg':999999})
    check('Quiz aprobado completa el módulo',code==200 and passed.get('aprobado') and passed['puntaje']==100)
    xp=scalar('SELECT xp_puntos FROM usuarios WHERE id=?',[users['aprendiz']['id']])
    check('Quiz duplicado no otorga XP doble',learner.post('/aprendiz/rap/guardar-quiz',{**common,**answers,'sesion_quiz_id':quiz_session})[1]==passed and scalar('SELECT xp_puntos FROM usuarios WHERE id=?',[users['aprendiz']['id']])==xp)
    check('Siguiente módulo se desbloquea',learner.request('/aprendiz/rap?id='+raps[1]['id'])[0]==200)
    check('Repetir conserva progreso histórico',learner.request('/aprendiz/rap?id='+rid+'&repetir=1')[0]==200 and float(scalar('SELECT porcentaje FROM progreso WHERE usuario_id=? AND rap_id=?',[users['aprendiz']['id'],rid]))==100)
    for path in ['/aprendiz/perfil','/aprendiz/glosario','/aprendiz/vocabulario']:
        code,body,_=learner.request(path); check('Pantalla '+path,code==200); verify_js(path.replace('/','-'),body)
    check('Marcar vocabulario con CSRF',learner.post('/aprendiz/rap/marcar-vocabulario',{'vocabulario_id':voc[0]['id']})[1].get('exito'))
    profile=learner.request('/aprendiz/perfil/actualizar',{'csrf_token':learner.csrf,'accion':'nombre','nombre_completo':'Carolina Ramirez'})
    check('Nombre no recibe tildes artificiales',profile[0]==302 and scalar('SELECT nombre_completo FROM usuarios WHERE id=?',[users['aprendiz']['id']])=='Carolina Ramirez')
    # Las vistas previas conservan todas sus rutas, sin escribir intentos o progreso.
    for role in ['admin','instructor']:
        client=clients[role]; code,body,_=client.request('/aprendiz/rap?id='+raps[-1]['id'])
        check('Vista previa '+role,code==200); verify_js('preview-'+role,body)
        check('Preview no guarda progreso '+role,client.post('/aprendiz/rap/guardar-progreso',{'rap_id':rid,'porcentaje':75})[1].get('preview') and scalar('SELECT COUNT(*) FROM progreso WHERE usuario_id=?',[users[role]['id']])==0)
    for path in ['/admin/usuarios','/admin/raps','/admin/catalogos','/admin/niveles','/admin/programas','/admin/gamificacion','/admin/vocabulario?rap_id='+rid,'/admin/ejercicios?rap_id='+rid,'/admin/dialogos?rap_id='+rid,'/admin/quizzes?rap_id='+rid]:
        code,body,_=admin.request(path); check('Administración '+path,code==200); verify_js('admin-'+path.split('/')[-1].split('?')[0],body)
    for path in ['/instructor/aprendices','/instructor/resultados','/instructor/raps','/instructor/niveles','/instructor/exportar']:
        check('Instructor '+path,instructor.request(path)[0]==200)
    # Publicar un RAP vacío se rechaza; el fixture se elimina sin tocar los existentes.
    empty_rid=str(uuid.uuid4())
    db('INSERT INTO rap (id,nivel_id,titulo,orden,activo) VALUES (?,?,?,99,0)',[empty_rid,nid,'Fixture vacío'])
    try:
        code,_,headers=admin.request('/admin/raps/toggle',{'csrf_token':admin.csrf,'id':empty_rid})
        check('Publicación incompleta se bloquea',code==302 and 'error=' in headers.get('Location','') and scalar('SELECT activo FROM rap WHERE id=?',[empty_rid])==0)
    finally: db('DELETE FROM rap WHERE id=?',[empty_rid])
    # Recorre los otros módulos y comprueba el avance compartido de sus RAPs.
    for order in [2,3,4]:
        rap=next(r for r in raps if r['orden']==order); module_id=rap['nivel_id']; module_rid=rap['id']
        code,page,_=learner.request('/aprendiz/rap?id='+module_rid)
        check('Módulo '+str(order)+' carga',code==200); verify_js('modulo-'+str(order),page)
        practice=json.loads(re.search(r'const practicaId = (.*?);',page).group(1))
        current={'rap_id':module_rid,'practica_id':practice}
        vocs=rows('SELECT v.id FROM vocabulario v JOIN rap r ON r.id=v.rap_id WHERE r.nivel_id=? AND r.activo=1 AND v.activo=1 ORDER BY v.rap_id,v.id LIMIT 3',[module_id])
        warmup=[{'en':v['id'],'es':v['id']} for v in vocs]
        check('Warm-Up M'+str(order),learner.post('/aprendiz/rap/guardar-progreso',{**current,'porcentaje':25,'pares':json.dumps(warmup)})[1].get('exito'))
        check('Absorption M'+str(order),learner.post('/aprendiz/rap/guardar-progreso',{**current,'porcentaje':50})[1].get('exito'))
        module_exercises=rows('SELECT e.* FROM ejercicio e JOIN rap r ON r.id=e.rap_id WHERE r.nivel_id=? AND r.activo=1 AND e.activo=1 ORDER BY e.rap_id,e.id',[module_id])
        for ex in module_exercises:
            opts=rows('SELECT id,texto,es_correcta FROM ejercicio_opcion WHERE ejercicio_id=? ORDER BY id',[ex['id']])
            payload={'ejercicio_id':ex['id'],'practica_id':practice,'solicitud_id':str(uuid.uuid4())}
            if ex['tipo'] in ('seleccion_multiple','role_play'):
                option=next(o for o in opts if o['es_correcta']); payload['opcion_id']=option['id']; payload['respuesta']=option['texto']
            elif ex['tipo']=='arrastrar_soltar': payload['respuesta']=json.dumps([dict(zip(('en','es'),o['texto'].split('=',1))) for o in opts])
            elif ex['tipo']=='ordenar_dialogo': payload['respuesta']=' | '.join(o['texto'] for o in opts)
            elif ex['tipo']=='escucha_escribe': payload['respuesta']=opts[0]['texto']
            else: payload['respuesta']=next(o['texto'] for o in opts if o['es_correcta'])
            code,data=learner.post('/aprendiz/rap/guardar-ejercicio',payload)
            check('Ejercicio M'+str(order)+' '+ex['tipo'],code==200 and data.get('es_correcto') is True)
        check('Práctica M'+str(order),learner.post('/aprendiz/rap/guardar-progreso',{**current,'porcentaje':75})[1].get('exito'))
        qs=rows('SELECT p.* FROM pregunta p JOIN quiz q ON q.id=p.quiz_id JOIN rap r ON r.id=q.rap_id WHERE r.nivel_id=? AND r.activo=1 AND q.activo=1 AND p.activo=1',[module_id])
        code,data=learner.post('/aprendiz/rap/iniciar-quiz',{**current,'preguntas_ids':json.dumps([p['id'] for p in qs])})
        check('Inicio quiz M'+str(order),code==200 and data.get('sesion_quiz_id'))
        answers={f"respuestas[{p['id']}]":p['respuesta_correcta'] for p in qs}
        code,data=learner.post('/aprendiz/rap/guardar-quiz',{**current,**answers,'sesion_quiz_id':data['sesion_quiz_id']})
        check('Quiz M'+str(order)+' califica todas las preguntas',code==200 and data.get('puntaje')==100 and len(data['detalles'])==len(qs))
        progresses=rows('SELECT p.porcentaje,p.completado FROM progreso p JOIN rap r ON r.id=p.rap_id WHERE p.usuario_id=? AND r.nivel_id=? AND r.activo=1',[users['aprendiz']['id'],module_id])
        check('Progreso compartido M'+str(order),len(progresses)==len([r for r in raps if r['orden']==order]) and all(float(p['porcentaje'])==100 and p['completado']==1 for p in progresses))

    # Contenido desechable independiente: ningún recurso real se edita o elimina.
    pending_module=str(uuid.uuid4()); pending_rid=str(uuid.uuid4()); pending_raps.append(pending_rid)
    db('INSERT INTO nivel(id,nombre,orden,activo,umbral_desbloqueo) VALUES (?,?,99,0,60)',[pending_module,'QA pendiente'])
    db('INSERT INTO rap(id,nivel_id,titulo,activo) VALUES (?,?,?,0)',[pending_rid,pending_module,'QA pendiente'])
    qsettings={'rap_id':pending_rid,'puntaje_minimo':60,'limite_tiempo_seg':300,'max_intentos':3}
    qpayload={**qsettings,'preguntas[0][texto]':'Original QA question','preguntas[0][opciones][0]':'A','preguntas[0][opciones][1]':'B','preguntas[0][respuesta_correcta]':'A'}
    check('Quiz de borrador válido se guarda',admin.post('/admin/quizzes/save',qpayload)[1].get('success'))
    pending_qid=scalar('SELECT id FROM quiz WHERE rap_id=?',[pending_rid])
    pending_pid=scalar('SELECT id FROM pregunta WHERE quiz_id=? AND activo=1',[pending_qid])
    exbase={'rap_id':pending_rid,'tipo':'seleccion_multiple','enunciado':'QA vacío'}
    code,data=admin.post('/admin/ejercicios/save',exbase)
    check('Ejercicio vacío rechazado sin insertar',code==422 and scalar('SELECT COUNT(*) FROM ejercicio WHERE rap_id=?',[pending_rid])==0)
    cases=[('seleccion_multiple',[('A',0),('B',0)],'QA'),('role_play',[('A',0),('B',0)],'QA'),('arrastrar_soltar',[('sin separador',0)],'QA'),('ordenar_dialogo',[('una sola línea',0)],'QA'),('escucha_escribe',[('A',0)],'QA'),('completar_frase',[('A',1)],'Sin hueco ni palabra objetivo')]
    for kind,opts,prompt in cases:
        payload={**exbase,'tipo':kind,'enunciado':prompt}
        for i,(text,correct) in enumerate(opts):payload.update({f'opciones[{i}][texto]':text,f'opciones[{i}][es_correcta]':correct})
        check('Formato inválido rechazado '+kind,admin.post('/admin/ejercicios/save',payload)[0]==422)
    check('Diálogo vacío rechazado',admin.post('/admin/dialogos/save',{'rap_id':pending_rid,'titulo':'QA vacío'})[0]==422 and scalar('SELECT COUNT(*) FROM dialogo WHERE rap_id=?',[pending_rid])==0)
    def publish_pending(): return admin.request('/admin/raps/toggle',{'id':pending_rid,'csrf_token':admin.csrf})
    check('Publicar sin vocabulario bloqueado','error=' in publish_pending()[2].get('Location','') and scalar('SELECT activo FROM rap WHERE id=?',[pending_rid])==0)
    pending_voc=[]
    for en,es in [('Hello QA','Hola QA'),('Morning QA','Mañana QA'),('Evening QA','Noche QA')]:
        vid=str(uuid.uuid4());pending_voc.append(vid)
        db('INSERT INTO vocabulario(id,rap_id,termino_en,termino_es) VALUES (?,?,?,?)',[vid,pending_rid,en,es])
    check('Publicar sin ejercicios bloqueado','error=' in publish_pending()[2].get('Location',''))
    dictation={**exbase,'tipo':'escucha_escribe','enunciado':'Dictado QA','opciones[0][texto]':'distractor','opciones[0][es_correcta]':0,'opciones[1][texto]':'correct target','opciones[1][es_correcta]':1}
    check('Dictado con distractor se guarda',admin.post('/admin/ejercicios/save',dictation)[1].get('success'))
    pending_ex=scalar('SELECT id FROM ejercicio WHERE rap_id=?',[pending_rid])
    # Forzar distractor antes de la respuesta correcta: el orden UUID no es el criterio.
    db('UPDATE ejercicio_opcion SET id=? WHERE ejercicio_id=? AND es_correcta=0',['0'+uuid.uuid4().hex[1:],pending_ex])
    db('UPDATE ejercicio_opcion SET id=? WHERE ejercicio_id=? AND es_correcta=1',['f'+uuid.uuid4().hex[1:],pending_ex])
    check('Publicar sin diálogos bloqueado','error=' in publish_pending()[2].get('Location',''))
    dialogue={'rap_id':pending_rid,'titulo':'Diálogo QA','turnos[0][hablante]':'Nurse','turnos[0][texto_en]':'Hello QA','turnos[0][texto_es]':'Hola QA'}
    check('Diálogo con turno se guarda',admin.post('/admin/dialogos/save',dialogue)[1].get('success'))
    pending_did=scalar('SELECT id FROM dialogo WHERE rap_id=?',[pending_rid])
    check('Publicación completa permitida','exito=' in publish_pending()[2].get('Location','') and scalar('SELECT activo FROM rap WHERE id=?',[pending_rid])==1)
    code,data=admin.post('/admin/quizzes/save',qsettings)
    check('Quiz publicado no queda vacío y revierte edición',code==422 and scalar('SELECT COUNT(*) FROM pregunta WHERE quiz_id=? AND activo=1',[pending_qid])==1)
    code,data=admin.post('/admin/quizzes/save',{**qpayload,'preguntas[0][respuesta_correcta]':''})
    check('Pregunta incompleta no borra contenido',code==422 and scalar('SELECT COUNT(*) FROM pregunta WHERE quiz_id=? AND activo=1',[pending_qid])==1)
    before_options=rows('SELECT id FROM ejercicio_opcion WHERE ejercicio_id=? ORDER BY id',[pending_ex])
    check('Edición inválida conserva ejercicio y opciones',admin.post('/admin/ejercicios/save',{**exbase,'id':pending_ex})[0]==422 and rows('SELECT id FROM ejercicio_opcion WHERE ejercicio_id=? ORDER BY id',[pending_ex])==before_options)
    check('Edición vacía conserva diálogo',admin.post('/admin/dialogos/save',{'rap_id':pending_rid,'id':pending_did,'titulo':'Vacío'})[0]==422 and scalar('SELECT COUNT(*) FROM turno_dialogo WHERE dialogo_id=? AND activo=1',[pending_did])==1)
    check('No se elimina último ejercicio publicado',admin.post('/admin/ejercicios/delete',{'id':pending_ex})[0]==422 and scalar('SELECT activo FROM ejercicio WHERE id=?',[pending_ex])==1)
    check('No se elimina último diálogo publicado',admin.post('/admin/dialogos/delete',{'id':pending_did})[0]==422 and scalar('SELECT activo FROM dialogo WHERE id=?',[pending_did])==1)
    code,_,headers=admin.request('/admin/vocabulario/toggle',{'csrf_token':admin.csrf,'rap_id':pending_rid,'id':pending_voc[0]})
    check('Warm-Up conserva sus tres palabras','error=' in headers.get('Location','') and scalar('SELECT activo FROM vocabulario WHERE id=?',[pending_voc[0]])==1)
    # Los borradores siguen permitiendo un quiz vacío.
    draft_rid=str(uuid.uuid4());pending_raps.append(draft_rid)
    db('INSERT INTO rap(id,nivel_id,titulo,orden,activo) VALUES (?,?,?,2,0)',[draft_rid,pending_module,'Borrador QA'])
    check('Quiz vacío permitido en borrador',admin.post('/admin/quizzes/save',{**qsettings,'rap_id':draft_rid})[1].get('success'))
    check('Roleplay conserva varios criterios válidos',admin.post('/admin/ejercicios/save',{'rap_id':draft_rid,'tipo':'role_play','enunciado':'QA autoevaluación','opciones[0][texto]':'Greeting','opciones[0][es_correcta]':1,'opciones[1][texto]':'Introduction','opciones[1][es_correcta]':1})[1].get('success'))
    db('UPDATE nivel SET activo=1 WHERE id=?',[pending_module])
    last_module=next(r['nivel_id'] for r in raps if r['orden']==4)
    db('UPDATE progreso p JOIN rap r ON r.id=p.rap_id SET p.porcentaje=75 WHERE p.usuario_id=? AND r.nivel_id=?',[users['aprendiz']['id'],last_module])
    code,page,_=learner.request('/aprendiz/rap?id='+pending_rid)
    check('Umbral 60 permite avance previo 75',code==200)
    check('Dictado presenta opción marcada correcta','data-correct="correct target"' in page and 'data-correct="distractor"' not in page)
    db('UPDATE nivel SET umbral_desbloqueo=90 WHERE id=?',[pending_module])
    check('Umbral 90 bloquea avance previo 75',learner.request('/aprendiz/rap?id='+pending_rid)[0]==403)
    db('UPDATE nivel SET umbral_desbloqueo=60 WHERE id=?',[pending_module])
    home_page=learner.request('/')[1];verify_js('home-umbral-configurado',home_page)
    check('Mapa usa umbral 60 en el mensaje','mostrarMensajeBloqueado(60,' in home_page)
    db('UPDATE progreso p JOIN rap r ON r.id=p.rap_id SET p.porcentaje=100 WHERE p.usuario_id=? AND r.nivel_id=?',[users['aprendiz']['id'],last_module])
    practice=re.search(r'const practicaId = "([^"]+)"',page).group(1)
    pending_common={'rap_id':pending_rid,'practica_id':practice}
    pairs=[{'en':vid,'es':vid} for vid in pending_voc]
    check('Warm-Up QA',learner.post('/aprendiz/rap/guardar-progreso',{**pending_common,'porcentaje':25,'pares':json.dumps(pairs)})[1].get('exito'))
    check('Absorption QA',learner.post('/aprendiz/rap/guardar-progreso',{**pending_common,'porcentaje':50})[1].get('exito'))
    attempt={'ejercicio_id':pending_ex,'practica_id':practice,'solicitud_id':str(uuid.uuid4()),'respuesta':'distractor'}
    check('Dictado rechaza distractor primero por ID',learner.post('/aprendiz/rap/guardar-ejercicio',attempt)[1].get('es_correcto') is False)
    attempt.update({'solicitud_id':str(uuid.uuid4()),'respuesta':'correct target'})
    check('Dictado acepta opción marcada correcta',learner.post('/aprendiz/rap/guardar-ejercicio',attempt)[1].get('es_correcto') is True)
    check('Práctica QA',learner.post('/aprendiz/rap/guardar-progreso',{**pending_common,'porcentaje':75})[1].get('exito'))
    code,started=learner.post('/aprendiz/rap/iniciar-quiz',{**pending_common,'preguntas_ids':json.dumps([pending_pid])})
    check('Inicio quiz QA con snapshot',code==200 and started.get('sesion_quiz_id'))
    changed={**qpayload,'puntaje_minimo':90,'preguntas[0][id]':pending_pid,'preguntas[0][texto]':'Changed QA question','preguntas[0][respuesta_correcta]':'B'}
    check('Edición válida de quiz publicado',admin.post('/admin/quizzes/save',changed)[1].get('success'))
    code,snapshot_result=learner.post('/aprendiz/rap/guardar-quiz',{**pending_common,'sesion_quiz_id':started['sesion_quiz_id'],f'respuestas[{pending_pid}]':'A'})
    check('Quiz iniciado conserva criterio y preguntas',code==200 and snapshot_result.get('puntaje')==100 and snapshot_result['detalles'][pending_pid]['texto']=='Original QA question')
    iq=rows('SELECT * FROM intento_quiz WHERE usuario_id=? AND quiz_id=?',[users['aprendiz']['id'],pending_qid])[0]
    check('Mínimo histórico persistido',float(iq['puntaje_minimo_original'])==60)
    check('Enunciado histórico persistido',scalar('SELECT texto_pregunta_original FROM respuesta_quiz WHERE intento_quiz_id=?',[iq['id']])=='Original QA question')
    import csv,io
    csv_body=instructor.request('/instructor/exportar')[1]
    csv_rows=list(csv.reader(io.StringIO(csv_body.lstrip('\ufeff')),delimiter=';'))
    historical=next(row for row in csv_rows if len(row)>12 and 'Original QA question' in row[-1])
    check('CSV conserva contexto anterior a edición',historical[7]=='60.00' and 'Changed QA question' not in historical[-1])
    legacy_id=str(uuid.uuid4())
    db('INSERT INTO intento_quiz(id,quiz_id,usuario_id,puntaje,aprobado,numero_intento) VALUES (?,?,?,70,1,2)',[legacy_id,pending_qid,users['aprendiz']['id']])
    db('INSERT INTO respuesta_quiz(id,intento_quiz_id,pregunta_id,respuesta_elegida,es_correcto) VALUES (?,?,?,?,1)',[str(uuid.uuid4()),legacy_id,pending_pid,'A'])
    csv_body=instructor.request('/instructor/exportar')[1]
    csv_rows=list(csv.reader(io.StringIO(csv_body.lstrip('\ufeff')),delimiter=';'))
    legacy=next(row for row in csv_rows if len(row)>12 and '[enunciado original no disponible]' in row[-1] and row[0]==users['aprendiz']['id'])
    check('Historial antiguo no inventa mínimo ni enunciado',legacy[7]=='' and 'Changed QA question' not in legacy[-1])

    # Cambiar una contraseña invalida las otras sesiones, incluso con actividad reciente.
    extra=Client(); login(extra,users['aprendiz'],password)
    old_hash=scalar('SELECT contrasena FROM usuarios WHERE id=?',[users['aprendiz']['id']])
    db('UPDATE usuarios SET contrasena=? WHERE id=?',[old_hash+'changed',users['aprendiz']['id']])
    check('Cambio de contraseña revoca otras sesiones',extra.json('/aprendiz/leaderboard')[0]==401)
    db('UPDATE usuarios SET contrasena=? WHERE id=?',[old_hash,users['aprendiz']['id']])
    check('Todas las pruebas completas',True)
finally:
    # Solo se eliminan las cuentas y las filas creadas por esta ejecución.
    for user in users.values():
        uid=user['id']
        db('DELETE rq FROM respuesta_quiz rq JOIN intento_quiz iq ON iq.id=rq.intento_quiz_id WHERE iq.usuario_id=?',[uid])
        for table in ['sesion_quiz','sesion_practica','intento_quiz','intento_ejercicio','vocabulario_marcado','insignia_usuario','puntaje_semanal','progreso']:
            db(f'DELETE FROM {table} WHERE usuario_id=?',[uid])
        db('DELETE FROM usuarios WHERE id=?',[uid])
    for pending_rid in pending_raps:
        for query in ['DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id=?)','DELETE FROM quiz WHERE rap_id=?','DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id=?)','DELETE FROM ejercicio WHERE rap_id=?','DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id=?)','DELETE FROM dialogo WHERE rap_id=?','DELETE FROM vocabulario WHERE rap_id=?','DELETE FROM rap WHERE id=?']:
            db(query,[pending_rid])
    if pending_module: db('DELETE FROM nivel WHERE id=?',[pending_module])
    (out/'results.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
print(f'{len(results)} verificaciones aprobadas.',flush=True)
