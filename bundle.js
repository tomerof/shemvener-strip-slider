const path = require('path')
const baseDir = __dirname
const distDir = `${baseDir}/dist`
const updateDir = `${baseDir}/update`
const fileSystem = require('fs')
const archiver = require('archiver')
const Comments = require('parse-comments')
const comments = new Comments()
const moment = require("moment");
const Sftp = require('ssh2-sftp-client');
require('dotenv').config()

const pluginName = path.basename(__dirname);
const pluginMainFile = path.basename(__dirname)+".php"
const pluginMainFileContent = fileSystem.readFileSync(`${baseDir}/${pluginMainFile}`)
const pluginMainFileComments = comments.parse(pluginMainFileContent.toString())
let version = "1.0.0";
if(pluginMainFileComments[0] && pluginMainFileComments[0].value){
    const versionStr = pluginMainFileComments[0].value.split('\n').filter(c => c.indexOf("Version") > -1)
    if(versionStr[0]){
        version = versionStr[0].split(':')[1].trim()
    }
}

const distZipName = `${pluginName}-${version}.zip`

if(!fileSystem.existsSync(distDir)){
    fileSystem.mkdirSync(distDir)
}

if(!fileSystem.existsSync(updateDir)){
    fileSystem.mkdirSync(updateDir)
}

const outputFilePath = `${distDir}/${distZipName}`;
const output = fileSystem.createWriteStream(outputFilePath)

const archive = archiver('zip', {
    zlib: { level: 9 } // Sets the compression level.
});

archive.directory(`assets/`, `${pluginName}/assets`, null);
archive.directory(`widgets/`, `${pluginName}/widgets`, null);
archive.file(`${pluginName}.php`, {name: `${pluginName}/${pluginName}.php`})

output.on('close', async () => {

    console.log(archive.pointer() + ' total bytes');
    console.log('archiver has been finalized and the output file descriptor has closed.');
    if(process.env.UPDATE_FTP_HOST && process.env.UPDATE_FTP_USER && process.env.UPDATE_FTP_PASSWORD){
        console.log('connecting to ftp...')
        let sftpClient = new Sftp();

        const ftpBasePath = '/public_html/plugins-updates'
        const ftpPluginDir = pluginName
        const ftpPluginPath = `${ftpBasePath}/${ftpPluginDir}`

        await sftpClient.connect({
            host: process.env.UPDATE_FTP_HOST,
            user: process.env.UPDATE_FTP_USER,
            password: process.env.UPDATE_FTP_PASSWORD,
        })

        console.log('ftp connected, uploading...')

        if(!await sftpClient.exists(`${ftpBasePath}/${ftpPluginDir}`)){
            await sftpClient.mkdir(`${ftpBasePath}/${ftpPluginDir}`)
        }

        await sftpClient.put(`${updateDir}/info.json`, `${ftpPluginPath}/info.json`)

        await sftpClient.put(outputFilePath, `${ftpPluginPath}/${distZipName}`)

        console.log('info & zip uploaded successfully')

        await sftpClient.end()
    }else{
        console.log('FTP not configured')
    }
});

archive.on('error', function(err){
    throw err;
});

archive.pipe(output);

archive.finalize().then(() => {});

// json data
const jsonObj = {
    name : "Shemvener Strip Slider",
    slug : pluginName,
    author : "<a href='https://netdesign.media'>Netdesign</a>",
    version,
    download_url : `https://netdesign.media/plugins-updates/${pluginName}/${distZipName}`,
    requires : "6.0",
    tested : "6.0",
    requires_php : "7.4",
    last_updated : moment().format('YYYY-MM-DD HH:mm:ss'),
    sections : {
        description : "A standalone plugin to show an infinite slider of deceased persons via an Elementor widget.",
        installation : "Click the activate button and watch the magic happen.",
    }
};

const jsonContent = JSON.stringify(jsonObj);

try {
    fileSystem.writeFileSync(`${updateDir}/info.json`, jsonContent, 'utf8');
    console.log('info.json file has been saved')
}catch (e){
    console.log("info.json creation error",e)
}
