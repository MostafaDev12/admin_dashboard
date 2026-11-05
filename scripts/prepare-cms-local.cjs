const fs=require('fs');
if(!fs.existsSync('.env.cms')){
 let env=fs.readFileSync('.env','utf8');
 const settings={APP_ENV:'cms',APP_DEBUG:'false',APP_URL:'http://127.0.0.1:8787',DB_CONNECTION:'sqlite',DB_DATABASE:process.cwd().replace(/\\/g,'/')+'/storage/app/website-v1.sqlite',MAIL_MAILER:'log',SESSION_DRIVER:'file',CACHE_DRIVER:'file'};
 for(const [key,value] of Object.entries(settings)){const re=new RegExp('^'+key+'=.*$','m');env=re.test(env)?env.replace(re,key+'='+value):env+'\n'+key+'='+value;}
 fs.writeFileSync('.env.cms',env);fs.closeSync(fs.openSync('storage/app/website-v1.sqlite','a'));
 console.log('Prepared isolated local CMS environment. Existing .env and MySQL data are unchanged.');
}
