<style id="resume-a4-base">
@page { size: A4; margin: 10mm; }
* { box-sizing: border-box; }
html {
    font-size: 9.25pt;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
body {
    margin: 0;
    padding: 10px 0 24px;
    background: #bdbdbd;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.a4-doc {
    width: 210mm;
    max-width: 100%;
    min-height: 297mm;
    margin: 0 auto 14px;
    background: #fff;
    color: #111;
    padding: 8mm 9mm 10mm;
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.16);
    overflow: visible;
}
.a4-doc .page,
.a4-doc .wrap {
    max-width: 100% !important;
    width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    min-height: 0 !important;
}
.a4-doc .page {
    align-items: flex-start;
}
.a4-doc .side {
    align-self: flex-start;
}
.a4-doc > .body {
    max-width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
}
h1, h2, h3 {
    break-after: avoid-page;
    page-break-after: avoid;
}
.block, .exp, .row, .card {
    break-inside: avoid;
    page-break-inside: avoid;
}
body.a4-body--dark {
    background: #2a2a2a !important;
}
.a4-doc--dark {
    background: #0f172a !important;
    color: #e2e8f0 !important;
    padding: 0 !important;
}
.a4-doc--dark .body {
    margin-top: 0 !important;
    border-radius: 0 !important;
}
@media print {
    html, body {
        background: #fff !important;
        padding: 0;
    }
    body { display: block; }
    body.a4-body--dark,
    .a4-doc--dark {
        background: #fff !important;
        color: #111 !important;
    }
    .a4-doc {
        width: 100%;
        max-width: 100%;
        min-height: 0;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
}
</style>
