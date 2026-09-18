"""Prueba PHPMailer contra SMTP de loopback, sin enviar correos a Internet."""
from pathlib import Path
import argparse, json, socketserver, subprocess, threading
from email import policy
from email.parser import BytesParser

parser=argparse.ArgumentParser()
parser.add_argument('--php',default='php')
parser.add_argument('--artifact-root',type=Path,required=True)
args=parser.parse_args()
root=Path(__file__).resolve().parents[1]
results=[]
def check(name,value):
    results.append({'name':name,'passed':bool(value)})
    print(('PASS ' if value else 'FAIL ')+name,flush=True)
    if not value: raise AssertionError(name)
def worker(**payload):
    run=subprocess.run([args.php,'-d','xdebug.mode=off',str(root/'tests/mail-worker.php')],input=json.dumps(payload),text=True,capture_output=True,check=True)
    return json.loads(run.stdout)
class SMTP(socketserver.StreamRequestHandler):
    def handle(self):
        self.wfile.write(b'220 local-test SMTP\r\n')
        while line:=self.rfile.readline():
            cmd=line.decode().strip().upper()
            if cmd.startswith(('EHLO','HELO')): self.wfile.write(b'250 local-test\r\n')
            elif cmd=='DATA':
                self.wfile.write(b'354 continue\r\n'); data=[]
                while (line:=self.rfile.readline()) not in (b'.\r\n',b''):
                    data.append(line[1:] if line.startswith(b'..') else line)
                self.server.messages.append(b''.join(data));self.wfile.write(b'250 accepted\r\n')
            elif cmd=='QUIT': self.wfile.write(b'221 bye\r\n');break
            else: self.wfile.write(b'250 ok\r\n')
class Server(socketserver.ThreadingTCPServer):
    allow_reuse_address=True
    daemon_threads=True

args.artifact_root.mkdir(parents=True,exist_ok=True)
try:
    with Server(('127.0.0.1',0),SMTP) as server:
        server.messages=[]
        thread=threading.Thread(target=server.serve_forever,daemon=True);thread.start()
        try:
            data=worker(action='send',port=server.server_address[1])
            check('SMTP local acepta cuatro mensajes',data['enviados']==[True]*4 and len(server.messages)==4)
            check('Remitente predeterminado es SMTP_USER',all('remitente@smashcode.test' in str(BytesParser(policy=policy.default).parsebytes(m)['From']) for m in server.messages))
            for index,raw in enumerate(server.messages):
                mail=BytesParser(policy=policy.default).parsebytes(raw)
                plain=mail.get_body(preferencelist=('plain',)).get_content()
                html=mail.get_body(preferencelist=('html',)).get_content()
                check(f'Mensaje {index+1}: UTF-8 y HTML/texto',mail.is_multipart() and '<!doctype html>' in html and 'SmashCode' in plain and all(p.get_content_charset()=='utf-8' for p in mail.iter_parts()))
                check(f'Mensaje {index+1}: botón y enlace conservados en texto','<a href="https://smashcode.test/campus/' in html and 'https://smashcode.test/campus/' in plain)
                check(f'Mensaje {index+1}: texto alternativo sin etiquetas','<table' not in plain and '<html' not in plain)
            check('Nombre preescapado sin doble codificación','Cristian &amp; Melo' in data['html'][0] and '&amp;amp;' not in data['html'][0])
            check('Nombre malicioso se presenta como texto','<script>' not in data['html'][1] and '&lt;script&gt;' in data['html'][1])
            check('Clave temporal escapa caracteres especiales','A&lt;&amp;&quot;2026!' in data['html'][2])
            check('Recuperación explica vencimiento y uso único','24 horas' in data['html'][1] and 'una vez' in data['html'][1])
            check('URL configurada conserva subcarpeta',data['url']=='https://smashcode.test/campus/login')
            check('URLs peligrosas se rechazan',data['urls_invalidas']==[True]*3)
            check('Forwarded Proto solo se acepta de proxy confiable',data['proxy_no_confiable'].startswith('http://') and data['proxy_confiable'].startswith('https://'))
            before=len(server.messages)
            disabled=worker(action='send',port=server.server_address[1],enabled='false')
            check('MAIL_ENABLED=false no abre conexión SMTP',disabled['enviados']==[False]*4 and not disabled['disponible'] and len(server.messages)==before)
            prod=worker(action='send',port=server.server_address[1],environment='production')
            check('Producción prohíbe transporte sin TLS/autenticación',prod['enviados']==[False]*4 and not prod['disponible'] and len(server.messages)==before)
        finally: server.shutdown();thread.join()
    unavailable=worker(action='send',port=1)
    check('Fallo de conexión devuelve false',unavailable['enviados']==[False]*4)
    fallback=worker(action='fallback')
    check('URL mal configurada no rompe alta ni entrega alternativa',fallback['bienvenida_fallida'] and fallback['alternativa_conservada'])
finally:
    (args.artifact_root/'results.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
