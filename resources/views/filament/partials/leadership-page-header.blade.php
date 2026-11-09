@once
<style>
    .leadership-content{--lead-ink:#173440;--lead-muted:#637782;--lead-line:#dce6ea;--lead-accent:#176d67;color:var(--lead-ink);font-family:Inter,"DM Sans",sans-serif;padding-bottom:28px}
    .leadership-hero{padding:26px 30px;border-radius:18px;background:linear-gradient(120deg,#102c3a,#184b4c 70%,#25736b);color:white;margin-bottom:18px}
    .leadership-hero .eyebrow{font-size:10px;letter-spacing:.15em;text-transform:uppercase;color:#b8d5d0;font-weight:800}
    .leadership-hero h1{font-size:27px;font-weight:850;margin:5px 0;letter-spacing:-.03em}
    .leadership-hero p{font-size:12px;color:#d7e5e4;margin:0;max-width:760px;line-height:1.65}
    .leadership-hero small{display:inline-block;margin-top:12px;color:#e2efec;font-size:10px}
    .leadership-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:14px 0}
    .leadership-card{background:#fff;border:1px solid var(--lead-line);border-radius:13px;padding:18px;box-shadow:0 2px 8px #14384408;min-width:0}
    .leadership-card h2,.leadership-card h3{font-size:14px;font-weight:800;margin:0 0 5px;color:var(--lead-ink)}
    .leadership-muted{font-size:11px;line-height:1.6;color:var(--lead-muted)}
    .leadership-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}
    .leadership-kpi{background:white;border:1px solid var(--lead-line);border-radius:12px;padding:14px}
    .leadership-kpi span{display:block;font-size:9px;letter-spacing:.08em;text-transform:uppercase;color:var(--lead-muted);font-weight:800}
    .leadership-kpi strong{display:block;font-size:23px;margin-top:6px;font-weight:850;color:var(--lead-ink)}
    .leadership-kpi small{display:block;margin-top:4px;font-size:10px;color:var(--lead-muted)}
    .leadership-table-wrap{overflow:auto;margin-top:12px}.leadership-table{width:100%;border-collapse:collapse;font-size:11px}.leadership-table th{text-align:left;color:#607580;font-size:9px;text-transform:uppercase;letter-spacing:.07em;padding:9px 8px;border-bottom:1px solid var(--lead-line);white-space:nowrap}.leadership-table td{padding:9px 8px;border-bottom:1px solid #edf1f2}.leadership-table .right{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
    .leadership-empty{padding:16px;border:1px dashed #cbd9dd;border-radius:10px;background:#f8fbfb;color:#607580;font-size:11px;line-height:1.6;margin-top:10px}.leadership-empty strong{display:block;color:var(--lead-ink);margin-bottom:4px}
    .leadership-demo{border:1px solid #e6b65f;background:linear-gradient(110deg,#fff9e9,#fffdf8);color:#79530d;border-radius:12px;padding:13px 15px;margin:12px 0;font-size:11px;line-height:1.55}.leadership-demo strong{display:block;font-size:11px;margin-bottom:3px;text-transform:uppercase;letter-spacing:.06em}
    .leadership-progress{height:8px;background:#e8eef0;border-radius:99px;overflow:hidden;margin:5px 0}.leadership-progress span{display:block;height:100%;background:linear-gradient(90deg,#197a72,#73ad85);border-radius:inherit}
    .leadership-map{height:360px;border:1px solid var(--lead-line);border-radius:12px;z-index:0}.leadership-actions{display:flex;justify-content:flex-end;gap:8px;margin:0 0 10px}.leadership-actions button{border:1px solid var(--lead-line);background:white;border-radius:8px;padding:8px 11px;color:var(--lead-ink);font-size:11px;font-weight:750;cursor:pointer}
    .leadership-source{margin-top:12px;padding-top:10px;border-top:1px solid #edf1f2;color:var(--lead-muted);font-size:10px;line-height:1.6}
    .sangihe-summary{max-width:1500px;margin-inline:auto}
    .sangihe-summary .leadership-hero{padding:22px 26px;margin-bottom:20px;border-radius:14px;background:linear-gradient(115deg,#163844,#205c5a);box-shadow:none}
    .sangihe-summary .leadership-hero h1{font-size:25px;margin:4px 0}
    .sangihe-summary .leadership-hero p{max-width:680px;font-size:13px}
    .sangihe-summary .leadership-hero small{margin-top:8px;font-size:11px}
    .sangihe-summary__section{margin-top:22px}
    .sangihe-summary__section-heading{display:flex;align-items:end;justify-content:space-between;gap:16px;margin:0 0 11px}
    .sangihe-summary__section-heading h2{margin:0;color:#173440;font-size:17px;font-weight:800;letter-spacing:-.02em}
    .sangihe-summary__section-heading p,.sangihe-summary__card-heading p{margin:3px 0 0;color:#637782;font-size:11px;line-height:1.5}
    .sangihe-summary__metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}
    .sangihe-summary__metric{min-width:0;padding:15px 17px;background:#fff;border:1px solid #dce6ea;border-radius:12px}
    .sangihe-summary__metric--primary{border-top:3px solid #176d67;padding-top:13px}
    .sangihe-summary__metric span{display:block;color:#637782;font-size:10px;font-weight:750}
    .sangihe-summary__metric strong{display:block;margin-top:5px;color:#173440;font-size:25px;font-weight:850;letter-spacing:-.03em;font-variant-numeric:tabular-nums}
    .sangihe-summary__metric small{display:block;margin-top:3px;color:#637782;font-size:10px;line-height:1.45}
    .sangihe-summary__section-heading--opd{align-items:center}
    .sangihe-summary__total-records{flex:none;border:1px solid #dce6ea;border-radius:999px;padding:6px 10px;background:#fff;color:#536b74;font-size:10px;font-weight:700}
    .sangihe-summary__opd-list{display:grid;grid-template-columns:1fr;gap:9px}
    .sangihe-summary__opd-card{overflow:hidden;background:#fff;border:1px solid #dce6ea;border-radius:12px;transition:border-color .15s ease,box-shadow .15s ease}
    .sangihe-summary__opd-card[open]{border-color:#a8c9c5;box-shadow:0 3px 12px #1734400a}
    .sangihe-summary__opd-summary{display:flex;align-items:center;gap:12px;min-height:70px;padding:12px 15px;cursor:pointer;list-style:none}
    .sangihe-summary__opd-summary::-webkit-details-marker,.sangihe-summary__dataset-card>summary::-webkit-details-marker{display:none}
    .sangihe-summary__opd-icon{display:grid;place-items:center;flex:none;width:38px;height:38px;border-radius:10px;background:#eaf4f2;color:#176d67;font-size:15px;font-weight:850}
    .sangihe-summary__opd-info{display:grid;gap:4px;min-width:0}
    .sangihe-summary__opd-info strong{color:#173440;font-size:12px;font-weight:800;line-height:1.4}
    .sangihe-summary__opd-info small,.sangihe-summary__opd-meta small{color:#71838a;font-size:10px;line-height:1.4}
    .sangihe-summary__opd-meta{display:flex;align-items:center;gap:13px;margin-left:auto;text-align:right}
    .sangihe-summary__chevron,.sangihe-summary__dataset-chevron{display:block;flex:none;width:8px;height:8px;border-right:2px solid #678087;border-bottom:2px solid #678087;transform:rotate(45deg);transition:transform .15s ease}
    .sangihe-summary__opd-card[open]>.sangihe-summary__opd-summary .sangihe-summary__chevron,.sangihe-summary__dataset-card[open]>summary .sangihe-summary__dataset-chevron{transform:rotate(225deg)}
    .sangihe-summary__dataset-list{display:grid;gap:7px;padding:0 14px 13px 65px}
    .sangihe-summary__dataset-card{overflow:hidden;border:1px solid #e5ecee;border-radius:9px;background:#fbfcfc}
    .sangihe-summary__dataset-card>summary{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 12px;cursor:pointer;list-style:none}
    .sangihe-summary__dataset-card>summary>span:first-child{display:grid;gap:4px;min-width:0}
    .sangihe-summary__dataset-card>summary strong{color:#29434c;font-size:11px;line-height:1.45}
    .sangihe-summary__dataset-card>summary small{color:#71838a;font-size:9px;line-height:1.4}
    .sangihe-summary__dataset-card[open]{background:#fff;border-color:#d5e2e4}
    .sangihe-summary__dataset-content{display:grid;gap:10px;padding:0 11px 11px}
    .sangihe-summary__dataset-content>.leadership-source{margin:0;padding-top:9px}
    .sangihe-summary__no-infographic{margin:0;padding:12px;border-radius:8px;background:#f5f8f9;color:#637782;font-size:10px;line-height:1.55}
    .sangihe-summary__dataset-content .min-w-0.rounded-xl{padding:14px;border-radius:9px;box-shadow:none}
    .sangihe-summary__dataset-content .min-w-0.rounded-xl h3{font-size:13px}
    .sangihe-summary__dataset-content .min-w-0.rounded-xl .text-3xl{font-size:24px}
    @media(max-width:900px){.leadership-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.leadership-grid{grid-template-columns:1fr}}
    @media(max-width:1000px){.sangihe-summary__metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.sangihe-summary .leadership-hero{padding:18px}.sangihe-summary .leadership-hero h1{font-size:22px}.sangihe-summary__metric{padding:12px}.sangihe-summary__metric strong{font-size:21px}.sangihe-summary__section-heading--opd{align-items:flex-start;flex-direction:column}.sangihe-summary__opd-summary{padding:11px;gap:9px}.sangihe-summary__opd-icon{width:34px;height:34px}.sangihe-summary__opd-meta small{display:none}.sangihe-summary__dataset-list{padding-left:12px}.sangihe-summary__dataset-content{padding-inline:8px}}
    @media(max-width:600px){.leadership-hero{padding:20px}.leadership-hero h1{font-size:23px}.leadership-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.leadership-kpi strong{font-size:19px}.leadership-map{height:280px}}
    @media print{.leadership-actions{display:none!important}.leadership-hero{print-color-adjust:exact;-webkit-print-color-adjust:exact}.leadership-card,.leadership-kpi{box-shadow:none;break-inside:avoid}}
</style>
@endonce
<header class="leadership-hero">
    <div class="eyebrow">Monitoring Pimpinan · Kabupaten Kepulauan Sangihe</div>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    @if (!empty($sourceNote))<small>{{ $sourceNote }}</small>@endif
</header>
