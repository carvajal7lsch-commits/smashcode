"""Mensajes de integraciones sin configurar y cuenta Google sin clave, solo local."""
from pathlib import Path
import argparse, json, re, subprocess, uuid
from urllib.request import build_opener, Request, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.error import HTTPError
from urllib.parse import urlencode, parse_qs, urlsplit
from http.cookiejar import CookieJar
from xml.etree import ElementTree

p=argparse.ArgumentParser();p.add_argument('--php',default='php');p.add_argument('--base-url',default='http://127.0.0.1:8097');p.add_argument('--artifact-root',type=Path,required=True);args=p.parse_args()
root=Path(__file__).resolve().parents[1];base=args.base_url;results=[]
if urlsplit(args.base_url).hostname not in ('localhost','127.0.0.1'): raise SystemExit('Solo servidor local')
env=dict(re.findall(r'^([A-Z_]+)=(.*)$',(root/'.env').read_text(encoding='utf-8'),re.M))
if env.get('APP_ENV')!='local' or env.get('MAIL_ENABLED')!='false' or env.get('GOOGLE_CLIENT_ID','').strip(): raise SystemExit('Esta prueba exige local, correo deshabilitado y OAuth sin configurar.')
datadir=env['LOCAL_MYSQL_DATADIR'].strip().strip('"\'')
subprocess.run([args.php,'-d','xdebug.mode=off',str(root/'tools/verify-local-db.php'),datadir,re.search(r'^DB_PUERTO=(.*)$',(root/'.env').read_text(),re.M).group(1).strip()],capture_output=True,text=True,check=True)
args.artifact_root.mkdir(parents=True,exist_ok=True)
def db(sql,params=None):
    r=subprocess.run([args.php,'-d','xdebug.mode=off',str(root/'tests/db-fixtures.php'),str(root)],input=json.dumps({'sql':sql,'params':params or []}),text=True,capture_output=True,check=True)
    return json.loads(r.stdout)['rows']
def check(name,value):
    results.append({'name':name,'passed':bool(value)});print(('PASS ' if value else 'FAIL ')+name,flush=True)
    if not value: raise AssertionError(name)
class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self,*a,**kw): return None
client=build_opener(HTTPCookieProcessor(CookieJar()),NoRedirect())
def request(path,data=None):
    try: r=client.open(Request(base+path,data=urlencode(data).encode() if data else None),timeout=15)
    except HTTPError as e: r=e
    with r:return r.code,r.read().decode('utf-8'),dict(r.headers)
def token(path): return re.search(r'name="csrf_token" value="([^"]+)"',request(path)[1]).group(1)
uid=str(uuid.uuid4());correo=uid+'@smashcode.test';created=False
try:
    db('INSERT INTO usuarios(id,nombre_completo,correo,contrasena,rol,activo,intentos_fallidos) VALUES (?,?,?,NULL,\'aprendiz\',1,0)',[uid,'Prueba integración',correo]);created=True
    code,body,_=request('/login/ingresar',{'csrf_token':token('/login'),'correo':correo,'contrasena':'Ejemplo2026!'})
    check('Cuenta sin clave indica Google o recuperación',code==200 and 'no tiene una contraseña creada' in body and 'Continuar con Google' in body)
    check('Cuenta sin clave no acumula intentos fallidos',db('SELECT intentos_fallidos FROM usuarios WHERE id=?',[uid])[0]['intentos_fallidos']==0)
    known=request('/recuperar/enviar',{'csrf_token':token('/recuperar'),'correo':correo})
    # El POST de recuperación usa la ruta registrada por la aplicación.
    if known[0]==404: raise RuntimeError('Verificar ruta de recuperación en index.php')
    unknown=request('/recuperar/enviar',{'csrf_token':token('/recuperar'),'correo':'no-existe-'+uid+'@smashcode.test'})
    check('Correo deshabilitado muestra aviso global sin enumeración',known[0]==unknown[0]==200 and 'recuperación por correo no está disponible' in known[1] and 'recuperación por correo no está disponible' in unknown[1])
    check('Sin SMTP no se emiten tokens inútiles',db('SELECT id FROM token_recuperacion WHERE usuario_id=?',[uid])==[])
    code,_,headers=request('/login/google')
    message=parse_qs(urlsplit(headers.get('Location','')).query).get('error',[''])[0]
    check('Google sin configurar ofrece login por correo',code==302 and 'no está disponible' in message and '.env' not in message)
    code,_,headers=request('/login/google/callback?state=alterado&code=falso')
    message=parse_qs(urlsplit(headers.get('Location','')).query).get('error',[''])[0]
    check('Callback inválido rechaza el state y explica reintento',code==302 and 'no se pudo validar' in message)
    code,body,_=request('/assets/icons/smashcode.svg');svg=ElementTree.fromstring(body)
    symbols={s.attrib['id'] for s in svg}
    check('Sprite SVG sirve todos los iconos locales',code==200 and {'medal','bolt','book','warning','stethoscope','flame','lock'}<=symbols)
    for path in ['/tools/preview-mails.php','/tests/mail-worker.php']:
        check('Herramienta interna bloqueada: '+path,request(path)[0] in (403,404))
finally:
    if created:
        db('DELETE FROM token_recuperacion WHERE usuario_id=?',[uid]);db('DELETE FROM usuarios WHERE id=?',[uid])
    (args.artifact_root/'results.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
