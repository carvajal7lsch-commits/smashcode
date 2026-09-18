"""Mapa, perfiles y contenido nuevo, usando exclusivamente fixtures locales propias."""
from pathlib import Path
import argparse,json,re,subprocess,uuid
from urllib.request import Request,build_opener,HTTPCookieProcessor,HTTPRedirectHandler
from urllib.error import HTTPError
from urllib.parse import urlencode,urlsplit
from http.cookiejar import CookieJar
p=argparse.ArgumentParser();p.add_argument('--php',default='php');p.add_argument('--base-url',default='http://127.0.0.1:8097');p.add_argument('--artifact-root',type=Path,required=True);args=p.parse_args()
root=Path(__file__).resolve().parents[1];checks=[]
env=dict(re.findall(r'^([A-Z_]+)=(.*)$',(root/'.env').read_text(),re.M))
if urlsplit(args.base_url).hostname not in ('localhost','127.0.0.1') or env.get('APP_ENV')!='local' or env.get('MAIL_ENABLED')!='false':raise SystemExit('Solo local con correo desactivado')
subprocess.run([args.php,'-d','xdebug.mode=off',str(root/'tools/verify-local-db.php'),env['LOCAL_MYSQL_DATADIR'].strip('"\''),env['DB_PUERTO']],capture_output=True,text=True,check=True)
def db(sql=None,params=None,action=None):
 r=subprocess.run([args.php,'-d','xdebug.mode=off',str(root/'tests/db-fixtures.php'),str(root)],input=json.dumps({'action':action} if action else {'sql':sql,'params':params or []}),capture_output=True,text=True,check=True);return json.loads(r.stdout)
def check(n,v):
 checks.append({'name':n,'passed':bool(v)});print(('PASS ' if v else 'FAIL ')+n,flush=True)
 if not v:raise AssertionError(n)
class NoRedirect(HTTPRedirectHandler):
 def redirect_request(self,*a,**k):return None
class Client:
 def __init__(self):self.c=build_opener(HTTPCookieProcessor(CookieJar()),NoRedirect())
 def req(self,path,data=None):
  try:r=self.c.open(Request(args.base_url+path,data=urlencode(data).encode() if data else None),timeout=15)
  except HTTPError as e:r=e
  with r:return r.code,r.read().decode('utf-8'),dict(r.headers)
 def token(self):return re.search(r'name="csrf_token" value="([^"]+)"',self.req('/login')[1]).group(1)
 def login(self,u,pwd):return self.req('/login/ingresar',{'csrf_token':self.token(),'correo':u['correo'],'contrasena':pwd})
def clean(body):return not re.search(r'(?:Warning|Deprecated|Fatal error)\s*</?[^>]*>\s*:|(?:Warning|Deprecated|Fatal error)\s*:',body)
f=db(action='init');users=f['users'];level=str(uuid.uuid4());rap=str(uuid.uuid4());exercise=None;word=None;created=False
try:
 clients={}
 for role,path in [('aprendiz','/'),('instructor','/instructor'),('admin','/admin')]:
  c=Client();r=c.login(users[role],f['password']);clients[role]=c
  check('Rol '+role+' abre su panel automáticamente',r[0]==302 and urlsplit(r[2]['Location']).path==path)
  page=c.req(path);check('Panel '+role+' sin advertencias PHP',page[0]==200 and clean(page[1]))
 learner=clients['aprendiz'];uid=users['aprendiz']['id']
 for xp,name in [(0,'Novato Clínico'),(1000,'Avanzado Clínico')]:
  db('UPDATE usuarios SET xp_puntos=? WHERE id=?',[xp,uid]);home=learner.req('/')[1];profile=learner.req('/aprendiz/perfil')[1]
  check('Rango '+name+' coincide en mapa y perfil',name in home and name in profile and clean(home) and clean(profile))
 eid=db('SELECT id FROM ejercicio WHERE activo=1 LIMIT 1')['rows'][0]['id']
 for days in (0,1):db('INSERT INTO intento_ejercicio(id,usuario_id,ejercicio_id,creado_en) VALUES (UUID(),?,?,DATE_SUB(NOW(),INTERVAL ? DAY))',[uid,eid,days])
 home=learner.req('/')[1]
 check('Racha real coincide en barra y panel',bool(re.search(r'Racha:\s*2\s*días',home)) and bool(re.search(r'<span>\s*2\s*días\s*</span>',home)))
 check('Ranking completo abre vista HTML del perfil','/aprendiz/perfil#leaderboard-lista' in home and '/aprendiz/leaderboard">VER TODO' not in home)
 admin=clients['admin'];csrf=json.loads(admin.req('/sesion/csrf')[1])['csrf_token']
 db('INSERT INTO nivel(id,nombre,orden,activo) VALUES (?,?,999,0)',[level,'QA actualización']);created=True
 db('INSERT INTO rap(id,nivel_id,titulo,orden,activo) VALUES (?,?,?,1,0)',[rap,level,'QA actualización'])
 seed=db('SELECT * FROM vocabulario WHERE activo=1 LIMIT 1')['rows'][0]
 data={k:seed.get(k) or '' for k in ('termino_en','termino_es','categoria_id','area_clinica_id','transcripcion_ipa','oracion_ejemplo','traduccion_ejemplo','nivel_dificultad')}
 data.update(csrf_token=csrf,rap_id=rap,etiquetas='QA, qa, búsqueda, BÚSQUEDA')
 r=admin.req('/admin/vocabulario/guardar',data);words=db('SELECT id,etiquetas FROM vocabulario WHERE rap_id=?',[rap])['rows'];word=words[0]['id'] if words else None
 check('Vocabulario con etiquetas se guarda sin duplicarlas',r[0]==302 and word is not None and words[0]['etiquetas'].casefold()=='qa, búsqueda')
 payload={'csrf_token':csrf,'rap_id':rap,'tipo':'seleccion_multiple','enunciado':'QA actualización','vocab_ayuda_id':word,'opciones[0][texto]':'Yes','opciones[0][es_correcta]':'1','opciones[1][texto]':'No'}
 r=admin.req('/admin/ejercicios/save',payload);items=db('SELECT id,vocab_ayuda_id FROM ejercicio WHERE rap_id=?',[rap])['rows'];exercise=items[0]['id'] if items else None
 check('Ayuda activa del mismo módulo se conserva',r[0]==200 and exercise is not None and items[0]['vocab_ayuda_id']==word)
 for label,key,value in [('otro módulo','vocab_ayuda_id',seed['id']),('inexistente','vocab_ayuda_id',str(uuid.uuid4())),('array','vocab_ayuda_id[]',word)]:
  bad=payload.copy();bad.update(id=exercise,enunciado='No debe guardarse');bad.pop('vocab_ayuda_id');bad[key]=value
  r=admin.req('/admin/ejercicios/save',bad);saved=db('SELECT enunciado,vocab_ayuda_id FROM ejercicio WHERE id=?',[exercise])['rows'][0]
  check('Ayuda '+label+' rechazada sin alterar el ejercicio',r[0]==422 and saved['enunciado']=='QA actualización' and saved['vocab_ayuda_id']==word)
finally:
 if created:
  db('DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id=?)',[rap]);db('DELETE FROM ejercicio WHERE rap_id=?',[rap]);db('DELETE FROM vocabulario WHERE rap_id=?',[rap]);db('DELETE FROM rap WHERE id=?',[rap]);db('DELETE FROM nivel WHERE id=?',[level])
 for u in users.values():db('DELETE FROM usuarios WHERE id=?',[u['id']])
 args.artifact_root.mkdir(parents=True,exist_ok=True);(args.artifact_root/'results.json').write_text(json.dumps(checks,ensure_ascii=False,indent=2),encoding='utf-8')
