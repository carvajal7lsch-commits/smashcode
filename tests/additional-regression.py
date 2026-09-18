"""Regresión local de entrega de claves, validación y archivos. Usa datos propios desechables."""
from pathlib import Path
import argparse, base64, html, io, wave
import json, re, subprocess, uuid
from urllib.request import build_opener, Request, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.error import HTTPError
from urllib.parse import urlencode, urlsplit
from http.cookiejar import CookieJar

parser=argparse.ArgumentParser()
parser.add_argument('--php',default='php')
parser.add_argument('--base-url',default='http://127.0.0.1:8097')
parser.add_argument('--artifact-root',type=Path,required=True)
args=parser.parse_args()
root=Path(__file__).resolve().parents[1]
php=args.php
base=args.base_url
if urlsplit(args.base_url).hostname not in ('localhost','127.0.0.1'): raise SystemExit('Solo servidor local')
args.artifact_root.mkdir(parents=True,exist_ok=True)
evidence=[]
def db(sql=None,params=None,action=None):
    payload={'action':action} if action else {'sql':sql,'params':params or []}
    result=subprocess.run([php,'-d','xdebug.mode=off',str(root/'tests/db-fixtures.php'),str(root)],input=json.dumps(payload),text=True,capture_output=True,check=True)
    return json.loads(result.stdout)
def rows(sql,params=None): return db(sql,params)['rows']
class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs): return None
class Client:
    def __init__(self): self.opener=build_opener(HTTPCookieProcessor(CookieJar()),NoRedirect())
    def request(self,path,data=None,headers=None):
        req=Request(base+path,data=(urlencode(data).encode() if isinstance(data,dict) else data),headers=headers or {})
        try: response=self.opener.open(req,timeout=20)
        except HTTPError as error: response=error
        with response:
            return response.code,response.read().decode('utf-8',errors='replace'),dict(response.headers)
    def token(self):
        return re.search(r'name="csrf_token" value="([^"]+)"',self.request('/login')[1]).group(1)
def check(name,condition):
    evidence.append({'name':name,'passed':bool(condition)})
    print(('PASS ' if condition else 'FAIL ')+name,flush=True)
    if not condition: raise AssertionError(name)
def login(client,email,password):
    return client.request('/login/ingresar',{'csrf_token':client.token(),'correo':email,'contrasena':password})
def token(client): return json.loads(client.request('/sesion/csrf')[1])['csrf_token']
def key_from_page(body):
    match=re.search(r'<code data-clave-temporal>([^<]+)</code>',body)
    return html.unescape(match.group(1)) if match else None
def multipart(client,path,fields,files):
    boundary='QA'+uuid.uuid4().hex;pieces=[]
    for name,value in fields.items():
        pieces.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n'.encode())
    for name,(filename,mime,content) in files.items():
        pieces.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"; filename="{filename}"\r\nContent-Type: {mime}\r\n\r\n'.encode()+content+b'\r\n')
    pieces.append(f'--{boundary}--\r\n'.encode())
    return client.request(path,b''.join(pieces),{'Content-Type':f'multipart/form-data; boundary={boundary}'})
def failed(response): return response[0]==302 and 'error=' in response[2].get('Location','')
def uploads(): return {p.resolve() for p in (root/'assets/uploads').glob('*/*') if p.is_file()}
def track_word_files(word):
    for field in ['audio_url','imagen_url']:
        if word.get(field): new_files.add((root/word[field].lstrip('/')).resolve())

# The helper requires the explicit isolated datadir, obtained from the local environment.
datadir=re.search(r'^LOCAL_MYSQL_DATADIR=(.*)$',(root/'.env').read_text(),re.M).group(1).strip().strip('"\'')
subprocess.run([php,'-d','xdebug.mode=off',str(root/'tools/verify-local-db.php'),datadir,re.search(r'^DB_PUERTO=(.*)$',(root/'.env').read_text(),re.M).group(1).strip()],capture_output=True,text=True,check=True)
fixture=db(action='init');users=fixture['users'];emails=[];word_ids=[];programs=[];new_files=set()
initial_files=uploads()
try:
    admin=Client();check('Admin de prueba inicia sesión',login(admin,users['admin']['correo'],fixture['password'])[0]==302);csrf=token(admin)
    program=rows('SELECT id FROM programa_formacion WHERE activo=1 AND eliminado=0 LIMIT 1')[0]['id']
    for label,changes in [('programa inexistente',{'programa_id':str(uuid.uuid4())}),('ficha larga',{'ficha_sena':'X'*51}),('nombre largo',{'nombre_completo':'X'*256}),('programa como array',{'programa_id[]':'x'})]:
        guest=Client();email='register.'+uuid.uuid4().hex+'@smashcode.test';emails.append(email)
        data={'csrf_token':guest.token(),'nombre_completo':'QA registro','correo':email,'contrasena':'QaRegistro2026','programa_id':program}
        if 'programa_id[]' in changes: data.pop('programa_id')
        data.update(changes);code,body,_=guest.request('/login/registrar',data)
        check('Registro rechaza '+label+' sin error SQL',code==200 and 'SQLSTATE' not in body and not rows('SELECT id FROM usuarios WHERE correo=?',[email]))
    for role in ['supervisor','']:
        email='role.'+uuid.uuid4().hex+'@smashcode.test';emails.append(email)
        response=admin.request('/admin/usuarios/guardar',{'csrf_token':csrf,'nombre_completo':'QA rol','correo':email,'rol':role})
        check('Alta rechaza rol inválido '+repr(role),failed(response) and not rows('SELECT id FROM usuarios WHERE correo=?',[email]))
    own=users['otroaprendiz'];previous=rows('SELECT nombre_completo,programa_id FROM usuarios WHERE id=?',[own['id']])[0]
    data={'csrf_token':csrf,'id':own['id'],'nombre_completo':'QA cambiar','correo':own['correo'],'rol':'aprendiz','programa_id':str(uuid.uuid4())}
    response=admin.request('/admin/usuarios/actualizar',data)
    check('Edición con programa inválido conserva datos',failed(response) and rows('SELECT nombre_completo,programa_id FROM usuarios WHERE id=?',[own['id']])[0]==previous)
    inactive=str(uuid.uuid4());programs.append(inactive);db('INSERT INTO programa_formacion(id,nombre,activo,eliminado) VALUES (?,?,0,0)',[inactive,'QA inactivo '+inactive])
    db('UPDATE usuarios SET programa_id=? WHERE id=?',[inactive,own['id']]);data['programa_id']=inactive
    check('Edición conserva programa inactivo ya asignado',admin.request('/admin/usuarios/actualizar',data)[2].get('Location','').endswith('exito=actualizado'))
    page=admin.request('/admin/usuarios')[1]
    check('Selector permite conservar programa inactivo',f'value="{inactive}" data-inactivo="1"' in page)
    guest=Client();email='inactive.'+uuid.uuid4().hex+'@smashcode.test';emails.append(email)
    code,body,_=guest.request('/login/registrar',{'csrf_token':guest.token(),'nombre_completo':'QA inactivo','correo':email,'contrasena':'QaRegistro2026','programa_id':inactive})
    check('Registro no asigna programa inactivo',not rows('SELECT id FROM usuarios WHERE correo=?',[email]) and 'programa activo' in body)
    # A valid new learner keeps the automatic role identification.
    guest=Client();email='valid.'+uuid.uuid4().hex+'@smashcode.test';emails.append(email)
    code,body,_=guest.request('/login/registrar',{'csrf_token':guest.token(),'nombre_completo':'QA válido','correo':email,'contrasena':'QaRegistro2026','programa_id':program})
    check('Registro válido permanece operativo',code==200 and rows('SELECT rol FROM usuarios WHERE correo=?',[email])[0]['rol']=='aprendiz')
    check('Registro válido permite ingresar',login(Client(),email,'QaRegistro2026')[0]==302)
    email='temporal.'+uuid.uuid4().hex+'@smashcode.test';emails.append(email)
    response=admin.request('/admin/usuarios/guardar',{'csrf_token':csrf,'nombre_completo':'QA temporal','correo':email,'rol':'instructor','programa_id':program})
    check('Alta avisa que el correo no salió','exito=correo_pendiente' in response[2].get('Location',''))
    check('Clave temporal no aparece en URL','clave=' not in response[2].get('Location',''))
    response=admin.request('/admin/usuarios');key=key_from_page(response[1]);check('Admin recibe alternativa de entrega',bool(key))
    check('Credenciales evitan caché','no-store' in response[2].get('Cache-Control',''))
    check('Clave se muestra una sola vez',key_from_page(admin.request('/admin/usuarios')[1]) is None)
    new_user=rows('SELECT id FROM usuarios WHERE correo=?',[email])[0]['id'];learner=Client()
    response=login(learner,email,key);check('Clave temporal permite login forzado',response[0]==302 and '/cambiar-clave' in response[2].get('Location',''))
    response=learner.request('/cambiar-clave/guardar',{'csrf_token':token(learner),'contrasena':'QaNueva2026','contrasena_confirmar':'QaNueva2026'})
    check('Cambio obligatorio abre panel instructor',response[0]==302 and '/instructor' in response[2].get('Location','') and learner.request('/instructor')[0]==200)
    old_hash=rows('SELECT contrasena FROM usuarios WHERE id=?',[new_user])[0]['contrasena']
    check('Reset sin CSRF no cambia contraseña',admin.request('/admin/usuarios/clave-temporal',{'id':new_user})[0]==302 and rows('SELECT contrasena FROM usuarios WHERE id=?',[new_user])[0]['contrasena']==old_hash)
    response=admin.request('/admin/usuarios/clave-temporal',{'csrf_token':csrf,'id':new_user});check('Admin puede recuperar entrega temporal','correo_pendiente' in response[2].get('Location',''))
    new_key=key_from_page(admin.request('/admin/usuarios')[1]);check('Reset genera otra clave',bool(new_key) and new_key!=key)
    check('Reset revoca sesión anterior',learner.request('/instructor')[0]==302)
    response=login(Client(),email,new_key);check('Nueva clave vuelve a exigir cambio','cambiar-clave' in response[2].get('Location',''))
    response=admin.request('/admin/usuarios/clave-temporal',{'csrf_token':csrf,'id':users['admin']['id']});check('Reset temporal excluye administradores',failed(response))
    regular=Client();login(regular,users['aprendiz']['correo'],fixture['password'])
    check('Aprendiz no puede generar claves',regular.request('/admin/usuarios/clave-temporal',{'csrf_token':token(regular),'id':new_user})[0]==302)
    rid=rows('SELECT id FROM rap WHERE activo=1 ORDER BY orden LIMIT 1')[0]['id'];seed=rows('SELECT * FROM vocabulario WHERE rap_id=? AND activo=1 LIMIT 1',[rid])[0]
    fields={key:seed[key] for key in ['categoria_id','area_clinica_id','transcripcion_ipa','oracion_ejemplo','traduccion_ejemplo','nivel_dificultad']}
    fields.update({'rap_id':rid,'termino_en':'QA-'+uuid.uuid4().hex,'termino_es':'Prueba subida','csrf_token':csrf})
    buffer=io.BytesIO()
    with wave.open(buffer,'wb') as wav: wav.setnchannels(1);wav.setsampwidth(2);wav.setframerate(8000);wav.writeframes(b'\0\0'*800)
    audio=('valid.wav','audio/wav',buffer.getvalue())
    png=('valid.png','image/png',base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jv1sAAAAASUVORK5CYII='))
    invalid=[('extensión',{'audio':('unsupported.txt','text/plain',b'QA')}),('contenido falso',{'audio':('fake.mp3','audio/mpeg',b'QA text')}),('campo incorrecto',{'imagen':audio}),('tamaño',{'audio':('big.wav','audio/wav',audio[2]+b'0'*(2*1024*1024))}),('SVG con evento',{'imagen':('event.svg','image/svg+xml',b'<svg xmlns="http://www.w3.org/2000/svg" onload="void(0)"/>')}),('SVG externo',{'imagen':('external.svg','image/svg+xml',b'<svg xmlns="http://www.w3.org/2000/svg"><image href="https://example.invalid/image.png"/></svg>')})]
    invalid.append(('SVG con hoja externa',{'imagen':('stylesheet.svg','image/svg+xml',b'<svg xmlns="http://www.w3.org/2000/svg"><?xml-stylesheet href="https://example.invalid/style.css"?></svg>')}))
    invalid.append(('SVG con CSS escapado',{'imagen':('style.svg','image/svg+xml',b'<svg xmlns="http://www.w3.org/2000/svg"><style>@i'+bytes([92])+b'mport "https://example.invalid/style.css";</style></svg>')}))
    for label,files in invalid:
        before=uploads();response=multipart(admin,'/admin/vocabulario/guardar',fields,files)
        check('Archivo inválido rechazado: '+label,failed(response) and not rows('SELECT id FROM vocabulario WHERE termino_en=?',[fields['termino_en']]) and uploads()==before)
    response=multipart(admin,'/admin/vocabulario/guardar',fields,{'audio':audio,'imagen':png});word=rows('SELECT * FROM vocabulario WHERE termino_en=?',[fields['termino_en']])[0];word_ids.append(word['id']);track_word_files(word)
    check('Audio e imagen válidos se guardan',response[0]==302 and 'exito=creado' in response[2].get('Location','') and word['audio_url'] and word['imagen_url'])
    check('Audio válido se sirve',admin.request(word['audio_url'])[0]==200)
    check('Imagen válida se sirve',admin.request(word['imagen_url'])[0]==200)
    fields.update({'id':word['id'],'termino_es':'No debe reemplazarse','audio_url_actual':'/false.mp3','imagen_url_actual':'/false.png'})
    response=multipart(admin,'/admin/vocabulario/actualizar',fields,invalid[0][1]);after=rows('SELECT * FROM vocabulario WHERE id=?',[word['id']])[0]
    check('Subida inválida conserva texto y ambos archivos',failed(response) and after['audio_url']==word['audio_url'] and after['imagen_url']==word['imagen_url'] and after['termino_es']==word['termino_es'])
    response=admin.request('/admin/vocabulario/actualizar',fields);after=rows('SELECT * FROM vocabulario WHERE id=?',[word['id']])[0]
    check('URLs previas se obtienen de la base',response[0]==302 and after['audio_url']==word['audio_url'] and after['imagen_url']==word['imagen_url'])
    before=uploads();response=multipart(admin,'/admin/vocabulario/actualizar',fields,{'audio':audio,'imagen':invalid[0][1]['audio']})
    check('Segundo archivo inválido no deja archivos nuevos',failed(response) and uploads()==before)
    svg=('static.svg','image/svg+xml',b'<svg xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="a"><stop stop-color="red"/></linearGradient></defs><rect width="1" height="1" fill="url(#a)"/></svg>')
    response=multipart(admin,'/admin/vocabulario/actualizar',fields,{'imagen':svg});track_word_files(rows('SELECT * FROM vocabulario WHERE id=?',[word['id']])[0])
    check('SVG estático conserva compatibilidad',response[0]==302 and 'exito=actualizado' in response[2].get('Location',''))
    rollback=dict(fields);rollback.pop('id');rollback['termino_en']='X'*256;before=uploads()
    response=multipart(admin,'/admin/vocabulario/guardar',rollback,{'audio':audio,'imagen':png})
    check('Error de base revierte archivos nuevos',failed(response) and uploads()==before and not rows('SELECT id FROM vocabulario WHERE termino_en=?',[rollback['termino_en']]))
    check('Contenido publicado existente sigue válido',db(action='validate-course')['failures']==[])
finally:
    for vid in word_ids: db('DELETE FROM vocabulario WHERE id=?',[vid])
    for email in emails:
        for user in rows('SELECT id FROM usuarios WHERE correo=?',[email]): db('DELETE FROM token_recuperacion WHERE usuario_id=?',[user['id']])
        db('DELETE FROM usuarios WHERE correo=?',[email])
    for user in users.values():
        db('DELETE FROM token_recuperacion WHERE usuario_id=?',[user['id']]);db('DELETE FROM usuarios WHERE id=?',[user['id']])
    for program_id in programs: db('DELETE FROM programa_formacion WHERE id=?',[program_id])
    allowed=(root/'assets/uploads').resolve()
    for file in new_files:
        if not file.is_relative_to(allowed) or file in initial_files: raise RuntimeError('Archivo fuera de los creados por esta prueba')
        file.unlink(missing_ok=True)
    (args.artifact_root/'results.json').write_text(json.dumps(evidence,ensure_ascii=False,indent=2),encoding='utf-8')
print(f'{len(evidence)} verificaciones adicionales aprobadas.',flush=True)
