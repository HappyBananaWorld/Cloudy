<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />
    <title>آپلود زیبا — Tus</title>

    <!-- Fredoka font -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg: #121923;
            --card: #1c2533aa;
            --accent: linear-gradient(90deg,#ff7f50,#1e90ff);
            --glass: rgba(255,255,255,0.05);
            --muted: rgba(255,255,255,0.7);
            --success: #22c55e;
            --danger: #ef4444;
            --shadow: 0 6px 20px rgba(0,0,0,0.6);
            --radius: 12px;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0;
            font-family: 'Fredoka', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: linear-gradient(120deg, #0f1724, #1e293b);
            color: #fff;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:16px;
        }

        .wrap{
            width:100%;
            max-width:700px;
            background: var(--card);
            border-radius:var(--radius);
            padding:24px;
            box-shadow:var(--shadow);
        }

        .card{padding:16px;border-radius:var(--radius);background:var(--glass);border:1px solid rgba(255,255,255,0.05);}

        .drop{
            margin-top:14px;
            border-radius:var(--radius);
            padding:20px;
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:12px;
            transition:all .25s ease;
            cursor:pointer;
            border: 2px dashed rgba(255,255,255,0.2);
            width:100%;
            text-align:center;
        }
        .drop.dragover{border-color:#ff7f50;box-shadow:0 4px 20px rgba(255,127,80,0.3);}
        .drop .left{display:flex;flex-direction:column;gap:6px;align-items:center}
        .drop .title{font-weight:600;font-size:16px}
        .drop .subtitle{font-size:12px;color:var(--muted)}
        .drop .file-info{font-size:13px;color:#fff;font-weight:600}

        .btn{padding:8px 14px;border-radius:8px;font-weight:600;cursor:pointer;border:none;min-width:90px;transition:all .2s ease}
        .btn.primary{background:var(--accent);color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.3)}
        .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.2);color:#fff}
        .btn[disabled]{opacity:.5;cursor:not-allowed}

        input[type=file]{display:none}

        .panel{margin-top:16px;display:none;flex-direction:column;gap:10px;align-items:stretch}
        .panel.show{display:flex}
        .progress-wrap{background:rgba(255,255,255,0.05);border-radius:10px;padding:8px}
        .progress{height:14px;background:rgba(255,255,255,0.1);border-radius:10px;overflow:hidden}
        .progress > i{height:100%;width:0%;background:var(--accent);transition:width .25s ease;border-radius:10px}
        .meta{display:flex;justify-content:space-between;font-size:12px;color:var(--muted)}
        .msg{padding:8px;border-radius:6px;font-size:12px}
        .msg.success{background:rgba(34,197,94,0.12);color:var(--success)}
        .msg.error{background:rgba(239,68,68,0.12);color:var(--danger)}

        .fade-exit{animation:fadeSlideOut .4s forwards}
        @keyframes fadeSlideOut{0%{opacity:1;transform:translateY(0)}100%{opacity:0;transform:translateY(-10px)}}

        @media(max-width:600px){
            .wrap{padding:16px}
            .drop{padding:16px;gap:8px}
            .drop .title{font-size:14px}
            .drop .subtitle{font-size:11px}
            .btn{padding:6px 12px;min-width:80px;font-size:13px}
            .progress-wrap{padding:6px}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card" id="uploadCard">
        <form id="uploadForm">
            <label for="fileInput" style="display:block;font-weight:700;margin-bottom:6px">فایل برای آپلود</label>
            <div class="drop" id="dropArea" tabindex="0">
                <div class="left">
                    <div class="title">باکس آپلود</div>
                    <div class="subtitle">کلیک کنید یا فایل را بکشید اینجا رها کنید</div>
                    <div class="file-info" id="fileName">هیچ فایلی انتخاب نشده</div>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center">
                    <button type="button" class="btn ghost" id="chooseBtn">انتخاب فایل</button>
                    <button type="button" class="btn primary" id="startBtn" disabled>ارسال</button>
                </div>
                <input id="fileInput" type="file" aria-label="انتخاب فایل برای آپلود" />
            </div>
        </form>
        <div class="panel" id="uploadPanel" aria-live="polite">
            <div class="meta">
                <div id="metaName">-</div>
                <div id="metaPercent">0%</div>
            </div>
            <div class="progress-wrap">
                <div class="progress" aria-hidden="false">
                    <i id="progressBar" style="width:0%"></i>
                </div>
            </div>
            <div id="statusMsg" class="msg" style="display:none"></div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/tus-js-client/dist/tus.js"></script>
<script>
    (function(){
        const ENDPOINT = document.body.dataset.endpoint || "{{route('upload')}}";
        const fileInput = document.getElementById('fileInput');
        const dropArea = document.getElementById('dropArea');
        const fileNameEl = document.getElementById('fileName');
        const chooseBtn = document.getElementById('chooseBtn');
        const startBtn = document.getElementById('startBtn');
        const uploadForm = document.getElementById('uploadForm');
        const uploadPanel = document.getElementById('uploadPanel');
        const progressBar = document.getElementById('progressBar');
        const metaName = document.getElementById('metaName');
        const metaPercent = document.getElementById('metaPercent');
        const statusMsg = document.getElementById('statusMsg');
        let chosenFile = null;
        let tusUpload = null;
        function humanSize(bytes){
            if(bytes===0) return '0 B';
            const k=1024,sizes=['B','KB','MB','GB','TB'],i=Math.floor(Math.log(bytes)/Math.log(k));
            return (bytes/Math.pow(k,i)).toFixed(2)+' '+sizes[i];
        }
        chooseBtn.addEventListener('click',()=>fileInput.click());
        fileInput.addEventListener('change',e=>{if(e.target.files&&e.target.files[0]) setFile(e.target.files[0]);});
        function setFile(file){chosenFile=file;fileNameEl.textContent=file.name+' • '+humanSize(file.size);startBtn.disabled=false;statusMsg.style.display='none';}
        ['dragenter','dragover'].forEach(evt=>dropArea.addEventListener(evt,e=>{e.preventDefault();e.stopPropagation();dropArea.classList.add('dragover');}));
        ['dragleave','drop'].forEach(evt=>dropArea.addEventListener(evt,e=>{e.preventDefault();e.stopPropagation();dropArea.classList.remove('dragover');}));
        dropArea.addEventListener('drop',e=>{const dt=e.dataTransfer;if(dt&&dt.files&&dt.files[0]){fileInput.files=dt.files;setFile(dt.files[0]);}});
        dropArea.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' ')fileInput.click();});
        startBtn.addEventListener('click',()=>{if(!chosenFile) return;uploadForm.classList.add('fade-exit');setTimeout(()=>uploadForm.style.display='none',400);uploadPanel.classList.add('show');metaName.textContent=chosenFile.name+' • '+humanSize(chosenFile.size);startTusUpload(chosenFile);});
        function startTusUpload(file){startBtn.disabled=true;chooseBtn.disabled=true;tusUpload=new tus.Upload(file,{endpoint:ENDPOINT,chunkSize:5*1024*1024,retryDelays:[0,1000,3000,5000],metadata:{filename:file.name,filetype:file.type||'application/octet-stream'},onError:error=>{console.error('Upload failed:',error);statusMsg.textContent='آپلود با خطا مواجه شد: '+(error.message||error);statusMsg.className='msg error';statusMsg.style.display='block';startBtn.disabled=false;chooseBtn.disabled=false;},onProgress:(bytesUploaded,bytesTotal)=>{const pct=((bytesUploaded/bytesTotal)*100).toFixed(2)+'%';progressBar.style.width=pct;metaPercent.textContent=pct;},onSuccess:()=>{progressBar.style.width='100%';metaPercent.textContent='100%';statusMsg.textContent='آپلود با موفقیت انجام شد!';statusMsg.className='msg success';statusMsg.style.display='block';if(tusUpload&&tusUpload.url){const a=document.createElement('a');a.href=tusUpload.url;a.target='_blank';a.rel='noopener noreferrer';a.textContent='دیدن فایل آپلود شده';a.style.display='inline-block';a.style.marginTop='6px';a.style.color='#fff';statusMsg.appendChild(document.createElement('br'));statusMsg.appendChild(a);}}});tusUpload.start();}
    })();
</script>
</body>
</html>
