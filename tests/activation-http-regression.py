"""Reenvío de activación validado por HTTP, solo datos locales desechables."""
from pathlib import Path
import argparse,json,re,subprocess
from urllib.request import Request,build_opener,HTTPCookieProcessor,HTTPRedirectHandler
from urllib.error import HTTPError
from urllib.parse import urlencode,urlsplit,parse_qs
from http.cookiejar import CookieJar
p=argparse.ArgumentParser();p.add_argument('--php',default='php');p.add_argument('--base-url',default='http://127.0.0.1:8097');p.add_argument('--artifact-root',type=Path,required=True);args=p.parse_args()
root=Path(__file__).resolve().parents[1];checks=[]
env=dict(re.findall(r'^([A-Z_]+)=(.*)$',(root/'.env').read_text(),re.M))
if urlsplit(args.base_url).hostname not in ('localhost','127.0.0.1') or env.get('APP_ENV')!='local' or env.get('MAIL_ENABLED')!='false':raise SystemExit('Solo local y correo deshabilitado')
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
fixture=db(action='init');users=fixture['users'];u=users['aprendiz'];uid=u['id']
def flag():return db('SELECT correo_verificado FROM usuarios WHERE id=?',[uid])['rows'][0]['correo_verificado']
def pending():
 db('UPDATE usuarios SET correo_verificado=0,activo=1,eliminado=0,bloqueado=0,rol=\'aprendiz\' WHERE id=?',[uid]);c=Client();r=c.login(u,fixture['password']);check('Login pendiente no crea sesión autenticada',r[0]==200 and 'no está activada' in r[1]);return c
try:
 c=pending();r=c.req('/activar/reenviar',{'csrf_token':'incorrecto'});check('Reenvío exige CSRF',r[0]==302 and flag()==0)
 c=pending();db('UPDATE usuarios SET activo=0 WHERE id=?',[uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Sesión pendiente no activa cuenta suspendida',r[0]==302 and flag()==0)
 c=pending();db('UPDATE usuarios SET eliminado=1 WHERE id=?',[uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Sesión pendiente no activa cuenta eliminada',r[0]==302 and flag()==0)
 c=pending();db('UPDATE usuarios SET rol=\'instructor\' WHERE id=?',[uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Cambio de rol invalida el reenvío pendiente',r[0]==302 and flag()==0)
 c=pending();db('UPDATE usuarios SET correo=? WHERE id=?',['cambio.'+u['correo'],uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Cambio de correo invalida el reenvío al destinatario anterior',r[0]==302 and flag()==0);db('UPDATE usuarios SET correo=? WHERE id=?',[u['correo'],uid])
 c=pending();original=db('SELECT contrasena FROM usuarios WHERE id=?',[uid])['rows'][0]['contrasena'];db('UPDATE usuarios SET contrasena=? WHERE id=?',[original+'cambio',uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Cambio de clave invalida el reenvío pendiente',r[0]==302 and flag()==0);db('UPDATE usuarios SET contrasena=? WHERE id=?',[original,uid])
 c=pending();db('INSERT INTO token_activacion(id,usuario_id,token,expira_en) VALUES (UUID(),?,?,DATE_ADD(NOW(),INTERVAL 1 DAY))',[uid,'token-'+uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});message=parse_qs(urlsplit(r[2].get('Location','')).query).get('error',[''])[0];check('Reenvío reciente se limita a un minuto',r[0]==302 and 'Espera un minuto' in message and flag()==0)
 db('UPDATE token_activacion SET creado_en=DATE_SUB(NOW(),INTERVAL 61 SECOND) WHERE usuario_id=?',[uid]);r=c.req('/activar/reenviar',{'csrf_token':c.token()});check('Demo local habilitada solo tras reenvío válido',r[0]==302 and flag()==1)
 check('Cuenta habilitada accede a su panel',Client().login(u,fixture['password'])[0]==302)
finally:
 for owned in users.values():db('DELETE FROM usuarios WHERE id=?',[owned['id']])
 args.artifact_root.mkdir(parents=True,exist_ok=True);(args.artifact_root/'results.json').write_text(json.dumps(checks,ensure_ascii=False,indent=2),encoding='utf-8')
