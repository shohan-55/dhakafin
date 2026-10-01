<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','DhakaFin Workspace')</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
.ws{min-height:100vh;background:#f5faf8}.wsnav{background:#062b27;color:white}.wsnavin{width:min(1180px,calc(100% - 32px));margin:auto;min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:18px}.wsmain{width:min(1180px,calc(100% - 32px));margin:auto;padding:34px 0 70px}.wsbrand{display:flex;gap:10px;align-items:center;font-weight:850}.wsgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.wscard{background:white;border:1px solid #dcebe7;border-radius:18px;padding:22px}.wscard h3{margin:0 0 8px}.muted{color:#60736f}.authwrap{min-height:100vh;display:grid;place-items:center;padding:28px}.authcard{width:min(460px,100%);background:white;border:1px solid #dcebe7;border-radius:24px;padding:30px;box-shadow:0 20px 60px rgba(7,59,53,.10)}.field{margin:16px 0}.field label{display:block;font-weight:700;font-size:.9rem;margin-bottom:7px}.field input{width:100%;min-height:46px;border:1px solid #cfdeda;border-radius:12px;padding:0 13px;font:inherit}.error{color:#a91d2a;font-size:.85rem;margin-top:7px}.codebox{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#eef8f5;border:1px solid #d5ebe5;border-radius:12px;padding:12px;word-break:break-all}.codes{display:grid;grid-template-columns:1fr 1fr;gap:10px}.code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#eef8f5;border-radius:10px;padding:10px;text-align:center}
@media(max-width:760px){.wsgrid{grid-template-columns:1fr}.codes{grid-template-columns:1fr}}
</style>
</head>
<body class="ws">@yield('body')</body>
</html>
