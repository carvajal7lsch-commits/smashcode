'use strict';
const fs=require('fs'),vm=require('vm'),assert=require('assert');
const source=fs.readFileSync(require('path').join(__dirname,'../app/Views/aprendiz/rap.php'),'utf8').replace(/\r\n/g,'\n');
const match=source.match(/  function escucharAyuda\(boton\) \{([\s\S]*?)\n  \}\n\n  \/\/ Conserva/);
assert(match,'Función de ayuda presente');
const code=('function escucharAyuda(boton) {'+match[1]+'\n}').replace('<?= json_encode(PROYECTO_PATH) ?>',JSON.stringify('/smashcode'));
const results=[];
function scenario(name,dataset,mode,expected){
 const calls=[];let failed;
 const context={window:{speechSynthesis:{cancel(){calls.push('cancel');}}},speakText(text){calls.push('voice:'+text);}};
 if(mode!=='unavailable')context.Audio=class{constructor(url){calls.push('audio:'+url);failed=()=>this.onerror();}play(){calls.push('play');if(mode==='throw')throw new Error('Audio inaccesible');return {catch(callback){if(mode==='reject')callback();}};}};
 vm.createContext(context);vm.runInContext(code,context);context.escucharAyuda({dataset});
 if(mode==='reject')failed();
 assert.deepStrictEqual(calls,expected,name);results.push({name,passed:true});
}
scenario('Ayuda reproduce archivo propio sin síntesis',{termino:'Patient',audio:'/assets/uploads/audios/example.mp3'},'ok',['cancel','audio:/smashcode/assets/uploads/audios/example.mp3','play']);
scenario('Ayuda sin archivo usa voz del vocabulario',{termino:'Patient'},'ok',['voice:Patient']);
scenario('Fallo del archivo usa voz una sola vez',{termino:'Patient',audio:'/missing.mp3'},'reject',['cancel','audio:/smashcode/missing.mp3','play','voice:Patient']);
scenario('Excepción de reproducción conserva alternativa',{termino:'Patient',audio:'/missing.mp3'},'throw',['cancel','audio:/smashcode/missing.mp3','play','voice:Patient']);
scenario('Navegador sin reproductor conserva síntesis',{termino:'Patient',audio:'/missing.mp3'},'unavailable',['voice:Patient']);
console.log(JSON.stringify(results,null,2));
