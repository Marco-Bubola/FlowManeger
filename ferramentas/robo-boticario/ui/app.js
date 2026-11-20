// Robô Boticário — tela
const $ = (s) => document.querySelector(s);
const TEMAS = [
  ["aurora", "Aurora", "linear-gradient(135deg,#7c3aed,#2563eb 55%,#db2777)"],
  ["oceano", "Oceano", "linear-gradient(135deg,#0891b2,#1d4ed8 55%,#14b8a6)"],
  ["por-do-sol", "Pôr do sol", "linear-gradient(135deg,#f97316,#e11d48 55%,#a21caf)"],
  ["floresta", "Floresta", "linear-gradient(135deg,#059669,#65a30d 55%,#0d9488)"],
  ["boticario", "Boticário", "linear-gradient(135deg,#00704a,#0f766e 55%,#be185d)"],
  ["rosa", "Rosa", "linear-gradient(135deg,#db2777,#9333ea 55%,#f472b6)"],
  ["noite", "Noite", "linear-gradient(135deg,#3f3f46,#1e293b 55%,#52525b)"],
  ["claro", "Claro", "linear-gradient(135deg,#c4b5fd,#93c5fd 55%,#fbcfe8)"],
];
const ORIGEM = {
  pasta: ["da pasta", "#a78bfa"], loja: ["da loja", "#34d399"], site: ["do site", "#f472b6"],
  ja: ["já tinha", "#94a3b8"], sem: ["sem foto", "#fb7185"], amostra: ["amostra", "#fbbf24"],
};
let cfg = {}, notas = [], desde = 0, filtro = "todos", ultimo = null, logTexto = [];

async function api(caminho, dados) {
  const r = await fetch(caminho, dados === undefined ? {} : {
    method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(dados),
  });
  return r.json();
}
function aviso(t, ms = 2600) {
  const a = $("#aviso"); a.textContent = t; a.classList.remove("escondido");
  clearTimeout(aviso.t); aviso.t = setTimeout(() => a.classList.add("escondido"), ms);
}
const esc = (t) => String(t ?? "").replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));

// ---------- temas
function aplicarTema(t) {
  document.documentElement.dataset.tema = t;
  document.querySelectorAll("#temas button").forEach((b) => b.classList.toggle("ativo", b.dataset.t === t));
}
$("#temas").innerHTML = TEMAS.map(([k, n, g]) => `<button data-t="${k}"><span class="amostra" style="background:${g}"></span>${n}</button>`).join("");
$("#temas").addEventListener("click", async (e) => {
  const b = e.target.closest("button"); if (!b) return;
  aplicarTema(b.dataset.t); cfg.tema = b.dataset.t; await api("/api/config", { tema: b.dataset.t });
});
$("#bt-tema").onclick = (e) => { e.stopPropagation(); $("#pop-tema").classList.toggle("escondido"); };
document.addEventListener("click", (e) => { if (!e.target.closest("#pop-tema")) $("#pop-tema").classList.add("escondido"); });

// ---------- config
function preencher() {
  document.querySelectorAll("[data-cfg]").forEach((el) => {
    const v = cfg[el.dataset.cfg] ?? "";
    if (el.type === "checkbox") el.checked = /^(sim|s|true|1|yes)$/i.test(v);
    else el.value = v;
  });
  $("#dica-pasta").textContent = cfg.pasta_imagens || "—";
  aplicarTema(cfg.tema || "aurora");
}
function colher(dentro) {
  const out = {};
  dentro.querySelectorAll("[data-cfg]").forEach((el) => { out[el.dataset.cfg] = el.type === "checkbox" ? (el.checked ? "sim" : "nao") : el.value; });
  return out;
}
document.querySelectorAll(".opcoes [data-cfg]").forEach((el) => el.addEventListener("change", async () => {
  cfg = (await api("/api/config", colher($(".opcoes")))).valores;
}));
$("#bt-ajustes").onclick = () => { preencher(); $("#veu").classList.remove("escondido"); };
$("#bt-fechar-ajustes").onclick = () => $("#veu").classList.add("escondido");
$("#veu").addEventListener("click", (e) => { if (e.target.id === "veu") $("#veu").classList.add("escondido"); });
$("#bt-salvar-ajustes").onclick = async () => {
  cfg = (await api("/api/config", colher($(".folha")))).valores; preencher();
  $("#veu").classList.add("escondido"); aviso("Ajustes salvos");
};
$("#bt-escolher-pasta").onclick = async () => {
  const inp = document.querySelector('.folha [data-cfg="pasta_imagens"]');
  aviso("Escolha a pasta na janela que abriu");
  const r = await api("/api/escolher_pasta", { atual: inp.value });
  if (r.pasta) inp.value = r.pasta;
};
$("#bt-abrir-robo").onclick = () => api("/api/abrir", { o: "robo" });
$("#bt-pasta").onclick = () => api("/api/abrir", { o: "pasta" });
$("#bt-relatorios").onclick = () => api("/api/abrir", { o: "relatorios" });
$("#bt-copiar").onclick = async () => { await navigator.clipboard.writeText(logTexto.join("\n")); aviso("Atividade copiada"); };

// ---------- nota
const soltar = $("#soltar");
function mostrarNota() {
  const n = notas.length;
  soltar.classList.toggle("tem", n > 0);
  $("#soltar-titulo").textContent = n === 0 ? "Solte as notas aqui" : n === 1 ? notas[0].nome : `${n} notas na fila`;
  $("#soltar-sub").textContent = n === 0 ? "ou clique para escolher (uma ou várias)"
    : n === 1 ? "Pronta para enviar. Clique para trocar."
    : notas.map((x, i) => `${i + 1}. ${x.nome}`).join("  ·  ");
  $("#bt-comecar span").textContent = n > 1 ? `Começar as ${n} notas` : "Começar";
}
function lerArquivos(lista) {
  const fs = Array.from(lista || []).filter((f) => /\.(pdf|csv)$/i.test(f.name));
  if (!fs.length) { if ((lista || []).length) aviso("Escolha o PDF (ou CSV) da nota"); return; }
  fs.sort((a, b) => a.name.localeCompare(b.name, "pt-BR", { numeric: true }));
  Promise.all(fs.map((f) => new Promise((ok) => { const r = new FileReader(); r.onload = () => ok({ nome: f.name, b64: r.result }); r.readAsDataURL(f); })))
    .then((lidas) => { notas = lidas; mostrarNota(); });
}
$("#arquivo").onchange = (e) => { lerArquivos(e.target.files); e.target.value = ""; };
["dragenter", "dragover"].forEach((ev) => document.addEventListener(ev, (e) => { e.preventDefault(); soltar.classList.add("sobre"); }));
["dragleave", "drop"].forEach((ev) => document.addEventListener(ev, (e) => { e.preventDefault(); soltar.classList.remove("sobre"); }));
document.addEventListener("drop", (e) => lerArquivos(e.dataTransfer.files));

async function comecar(semNota) {
  if (!semNota && !notas.length) { $("#arquivo").click(); return; }
  const r = await api("/api/iniciar", { notas: semNota ? [] : notas });
  if (!r.ok) return aviso(r.erro || "Não deu para começar");
  desde = 0; $("#log").innerHTML = ""; logTexto = [];
  notas = []; mostrarNota();
}
$("#bt-comecar").onclick = () => comecar(false);
$("#bt-sem-nota").onclick = () => comecar(true);
$("#bt-parar").onclick = async () => { if (confirm("Parar o robô agora?")) await api("/api/parar", {}); };

// ---------- filtros
$("#filtros").addEventListener("click", (e) => {
  const b = e.target.closest("button"); if (!b) return;
  filtro = b.dataset.f; document.querySelectorAll("#filtros button").forEach((x) => x.classList.toggle("ativo", x === b));
  ultimo = null;
});

// ---------- desenho
const ICONE_CAIXA = '<svg viewBox="0 0 24 24"><path d="M21 8 12 3 3 8v8l9 5 9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>';
const ICONE_GIRA = '<svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>';
function semBarras(p) { return (p.codigo_barras || "").startsWith("não achou"); }
function cardProduto(p) {
  const o = ORIGEM[p.origem] || null;
  let foto = `<div class="foto vazia">${ICONE_CAIXA}</div>`;
  if (p.estado === "trabalhando") foto = `<div class="foto vazia gira">${ICONE_GIRA}</div>`;
  else if (p.arquivo) foto = `<div class="foto"><img loading="lazy" src="/api/foto?arq=${encodeURIComponent(p.arquivo)}" onerror="this.parentNode.className='foto vazia';this.remove()"></div>`;
  const tags = [];
  if (p.estado === "trabalhando") tags.push(`<span class="tag" style="--c:var(--a1)">procurando…</span>`);
  else if (p.estado === "fila") tags.push(`<span class="tag" style="--c:#94a3b8">na fila</span>`);
  else if (o) tags.push(`<span class="tag" style="--c:${o[1]}">${o[0]}</span>`);
  if (p.estado === "erro") tags.push(`<span class="tag" style="--c:#fb7185">não entrou no card</span>`);
  const cb = p.codigo_barras || "";
  if (cb && cb !== "") {
    if (semBarras(p)) tags.push(`<span class="tag" style="--c:#fb923c">sem cód. de barras</span>`);
    else tags.push(`<span class="tag" style="--c:#60a5fa">${esc(cb.replace("já tinha ", ""))}</span>`);
  }
  return `<div class="prod ${p.estado}" title="${esc(p.resultado)}">${foto}<div class="info"><span class="cod">${esc(p.codigo)}</span><span class="nome">${esc(p.nome)}</span><div class="tags">${tags.join("")}</div></div></div>`;
}
function desenharProdutos(lista) {
  const chave = JSON.stringify([lista, filtro]);
  if (chave === ultimo) return; ultimo = chave;
  const f = lista.filter((p) => filtro === "todos" || (filtro === "sem" && p.origem === "sem") || (filtro === "barras" && semBarras(p))
    || (filtro === "ok" && ["pasta", "loja", "site", "ja"].includes(p.origem)));
  if (!lista.length) return;
  $("#produtos").innerHTML = f.length ? f.map(cardProduto).join("")
    : `<div class="vazio"><b>Nada aqui</b><small>Nenhum produto neste filtro.</small></div>`;
  const atual = document.querySelector(".prod.trabalhando");
  if (atual) atual.scrollIntoView({ block: "nearest", behavior: "smooth" });
}
function classeLog(t) {
  if (/^\[\d+\/\d+\]|^====/.test(t)) return "l-tit";
  if (/OK:|código de barras: \d|salvo em|foto da loja/.test(t)) return "l-ok";
  if (/sem imagem|não achei|ERRO|FALHOU|não consegui/i.test(t)) return "l-sem";
  if (/^\s{2,}/.test(t)) return "l-sub";
  return "";
}
function adicionarLog(linhas) {
  if (!linhas.length) return;
  const log = $("#log"), embaixo = log.scrollHeight - log.scrollTop - log.clientHeight < 40;
  const frag = document.createDocumentFragment();
  for (const l of linhas) {
    const d = document.createElement("div"); d.className = classeLog(l.t); d.textContent = l.t || " "; frag.appendChild(d); logTexto.push(l.t);
  }
  log.appendChild(frag);
  while (log.childNodes.length > 800) log.removeChild(log.firstChild);
  if (embaixo) log.scrollTop = log.scrollHeight;
}
function passos(n) {
  document.querySelectorAll("#passos li").forEach((li) => {
    const p = +li.dataset.p; li.classList.toggle("ativo", p === n); li.classList.toggle("feito", p < n);
  });
}
function contas(lista) {
  const c = { pasta: 0, loja: 0, site: 0, sem: 0, barras: 0 };
  for (const p of lista) {
    if (c[p.origem] !== undefined) c[p.origem]++;
    if (p.codigo_barras && !semBarras(p)) c.barras++;
  }
  document.querySelectorAll("#contas b").forEach((b) => (b.textContent = c[b.dataset.k]));
}
function desenharAcao(a) {
  const el = $("#acao");
  if (!a) { el.classList.add("escondido"); return; }
  el.classList.remove("escondido");
  el.classList.toggle("verde", a.tipo === "conferir");
  $("#acao-ico").textContent = a.tipo === "conferir" ? "✓" : "!";
  $("#acao-titulo").textContent = a.titulo; $("#acao-texto").textContent = a.texto;
  const chave = JSON.stringify(a);
  if (el.dataset.k !== chave) {
    el.dataset.k = chave;
    $("#acao-bts").innerHTML = (a.botoes || []).map((b) => `<button data-v="${esc(b)}">${esc(b)}</button>`).join("");
  }
}
$("#acao-bts").addEventListener("click", async (e) => {
  const b = e.target.closest("button"); if (!b) return;
  b.disabled = true; await api("/api/responder", { valor: b.dataset.v });
});

function desenhar(e) {
  const total = e.produtos.length, feitos = e.produtos.filter((p) => !["fila", "trabalhando"].includes(p.estado)).length;
  const rodando = e.fase === "rodando", conferir = e.fase === "conferir";
  $("#anel").style.strokeDashoffset = 326.7 * (1 - (total ? feitos / total : 0));
  $("#anel-num").textContent = total ? feitos : 0;
  $("#anel-de").textContent = `de ${total}`;
  const atual = e.produtos[e.atual - 1];
  const pil = $("#pil-estado");
  pil.className = "pilula" + (e.acao && e.acao.tipo !== "conferir" ? " espera" : rodando ? " rodando" : e.fase === "erro" ? " erro" : "");
  let rot = "Tudo pronto", tit = "Envie uma nota para começar", sub = "O robô abre o FlowManeger no Chrome dele, coloca as fotos e os códigos de barras nos cards. Você só confere e salva.";
  if (rodando && !total) { rot = "Começando"; tit = "Abrindo o FlowManeger…"; sub = "Pode acompanhar na janela do Chrome do robô."; pil.querySelector("em").textContent = "Trabalhando"; }
  else if (rodando) { rot = `Produto ${e.atual} de ${total}`; tit = atual ? atual.nome : "Trabalhando…"; sub = atual ? `Código ${atual.codigo}` : ""; pil.querySelector("em").textContent = "Trabalhando"; }
  else if (conferir) { rot = "Terminou"; const comFoto = e.produtos.filter((p) => ["pasta", "loja", "site", "ja"].includes(p.origem)).length;
    tit = `${comFoto} de ${total} produtos com foto`; sub = "Falta só você conferir os cards no Chrome do robô e salvar."; pil.querySelector("em").textContent = "Conferir"; }
  else if (e.fase === "erro") { rot = "Parou com erro"; tit = e.mensagem || "Algo deu errado"; sub = "Veja a atividade ao lado. Dá para tentar de novo."; pil.querySelector("em").textContent = "Erro"; }
  else if (e.fase === "parado") { rot = "Parado"; tit = "Você parou o robô"; sub = "Pode começar de novo quando quiser."; pil.querySelector("em").textContent = "Parado"; }
  else { pil.querySelector("em").textContent = total ? "Terminado" : "Pronto"; if (total) { rot = "Terminado"; tit = "Pronto! Pode enviar outra nota"; sub = e.relatorio ? `Relatório: ${e.relatorio}` : ""; } }
  if (e.acao && e.acao.tipo !== "conferir") pil.querySelector("em").textContent = "Esperando você";
  const fila = e.fila || [0, 0, ""];
  if (fila[1] > 1 && (rodando || conferir)) rot = `Nota ${fila[0]} de ${fila[1]} · ${rot}`;
  $("#heroi-rotulo").textContent = rot; $("#heroi-titulo").textContent = tit; $("#heroi-sub").textContent = sub;
  passos(rodando || e.acao && e.acao.tipo !== "conferir" ? 2 : conferir ? 3 : 1);
  const ocupado = rodando || conferir;
  $("#bt-comecar").disabled = ocupado; $("#bt-sem-nota").disabled = ocupado;
  $("#bt-parar").classList.toggle("escondido", !ocupado);
  contas(e.produtos); desenharProdutos(e.produtos); desenharAcao(e.acao);
}

async function ciclo() {
  try {
    const e = await api(`/api/estado?desde=${desde}`);
    adicionarLog(e.logs); desde = e.seq; desenhar(e);
    if (e.novidades && !ciclo.novidades) {
      ciclo.novidades = true;
      aviso(`Robô atualizado sozinho para a versão ${e.novidades.versao}. As novidades estão na atividade.`, 9000);
    }
    if (e.nota_inicial && !ciclo.inicial) {
      ciclo.inicial = true; notas = [{ nome: e.nota_inicial.split(/[\\/]/).pop(), caminho: e.nota_inicial }]; mostrarNota();
    }
  } catch (err) { /* robô fechado */ }
  setTimeout(ciclo, 900);
}
(async () => { cfg = await api("/api/config"); preencher(); ciclo(); })();

// ---------- arrumar a pasta de imagens
const arr = { aba: "repetidas", res: null, timer: null };
const fotoUrl = (n) => `/api/foto?arq=${encodeURIComponent(n)}&t=${arr.res ? arr.res.quando : ""}`;
const kb = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + " MB" : Math.round(b / 1024) + " KB");
const DICAS = {
  repetidas: "Fotos iguais ou do mesmo produto. A verde fica; as marcadas vão para a subpasta _repetidas (nada é apagado).",
  nomes: "Nomes que não dizem o produto, cópias \"(1)\" ou foto com a extensão errada. Confira o nome novo e deixe marcado o que quer trocar.",
  pequenas: "Fotos com menos de 500 pixels ficam borradas no catálogo. Vale trocar por uma maior quando puder.",
  sem_foto: "Produtos das notas que já passaram pelo robô e continuam sem foto na pasta.",
};
function abrirArrumar() {
  $("#veu-arrumar").classList.remove("escondido");
  if (!arr.res) api("/api/arrumar/analisar", {}).then(acompanharArrumar); else desenharArrumar();
}
$("#bt-arrumar").onclick = abrirArrumar;
$("#bt-fechar-arrumar").onclick = () => $("#veu-arrumar").classList.add("escondido");
$("#veu-arrumar").addEventListener("click", (e) => { if (e.target.id === "veu-arrumar") $("#veu-arrumar").classList.add("escondido"); });
$("#bt-arr-denovo").onclick = () => api("/api/arrumar/analisar", {}).then(acompanharArrumar);
$("#bt-arr-abrir").onclick = () => api("/api/abrir", { o: "pasta" });
$("#arr-resumo").addEventListener("click", (e) => {
  const b = e.target.closest("button"); if (!b) return;
  arr.aba = b.dataset.a; desenharArrumar();
});
async function acompanharArrumar() {
  clearTimeout(arr.timer);
  const e = await api("/api/arrumar");
  if (e.rodando) {
    $("#arr-lista").innerHTML = `<div class="arr-carregando"><i></i><span>${esc(e.msg || "Olhando a pasta…")}</span></div>`;
    $("#bt-arr-aplicar").disabled = true;
    arr.timer = setTimeout(acompanharArrumar, 600); return;
  }
  if (e.erro) { $("#arr-lista").innerHTML = `<div class="vazio"><b>Não deu para olhar a pasta</b><small>${esc(e.erro)}</small></div>`; return; }
  arr.res = e.resultado; desenharArrumar();
}
function desenharArrumar() {
  const r = arr.res; if (!r) return;
  $("#arr-sub").textContent = `${r.pasta} · ${r.total} fotos · olhada às ${r.quando}`;
  const n = { repetidas: r.repetidas.reduce((s, g) => s + g.sobram.length, 0), nomes: r.nomes.length, pequenas: r.pequenas.length, sem_foto: r.sem_foto.length };
  document.querySelectorAll("#arr-resumo button").forEach((b) => { b.classList.toggle("ativo", b.dataset.a === arr.aba); b.querySelector("b").textContent = n[b.dataset.a]; });
  $("#arr-dica").textContent = DICAS[arr.aba];
  const L = $("#arr-lista"); let h = "";
  if (arr.aba === "repetidas") {
    h = r.repetidas.map((g) => `<div class="arr-grupo">
      <div class="arr-foto"><div class="img"><img loading="lazy" src="${fotoUrl(g.fica.arq)}"></div><span class="selo">Fica</span>
        <span class="nm">${esc(g.fica.arq)}</span><small>${g.fica.w}×${g.fica.h} · ${kb(g.fica.bytes)}</small></div>
      ${g.sobram.map((s) => `<div class="arr-foto sai"><div class="img"><img loading="lazy" src="${fotoUrl(s.arq)}"></div>
        <label class="marca-mover"><input type="checkbox" data-mover="${esc(s.arq)}" checked> tirar</label>
        <span class="nm">${esc(s.arq)}</span><small>${g.tipo === "iguais" ? "foto idêntica" : "mesmo produto"} · ${s.w}×${s.h} · ${kb(s.bytes)}</small></div>`).join("")}
    </div>`).join("");
  } else if (arr.aba === "nomes") {
    h = r.nomes.map((x) => `<div class="arr-linha">
      <input type="checkbox" data-de="${esc(x.arq)}" ${x.sugestao ? "checked" : ""}>
      <div class="img"><img loading="lazy" src="${fotoUrl(x.arq)}"></div>
      <div class="txt"><b>${esc(x.arq)}</b><small>${esc(x.motivo)}</small>
        <input type="text" data-para="${esc(x.arq)}" value="${esc(x.sugestao)}" placeholder="Digite o nome do produto (ex.: EUD DES COL VELVET SOUL 100ml.jpg)"></div></div>`).join("");
  } else if (arr.aba === "pequenas") {
    h = r.pequenas.map((x) => `<div class="arr-linha" style="grid-template-columns:64px 1fr">
      <div class="img"><img loading="lazy" src="${fotoUrl(x.arq)}"></div>
      <div class="txt"><b>${esc(x.arq)}</b><small>${esc(x.motivo)} · ${kb(x.bytes)}</small></div></div>`).join("");
  } else {
    h = (r.sem_foto.length ? `<div><button class="bt-mini" id="bt-arr-copiar">Copiar a lista</button></div>` : "") +
      r.sem_foto.map((x) => `<div class="arr-linha sem"><span class="cod">${esc(x.codigo)}</span><div class="txt"><b>${esc(x.nome)}</b></div></div>`).join("");
  }
  L.innerHTML = h || `<div class="vazio"><b>Tudo certo aqui</b><small>Nada para arrumar nesta parte.</small></div>`;
  const c = $("#bt-arr-copiar");
  if (c) c.onclick = async () => { await navigator.clipboard.writeText(r.sem_foto.map((x) => `${x.codigo}\t${x.nome}`).join("\n")); aviso("Lista copiada"); };
  L.querySelectorAll("[data-mover]").forEach((cb) => cb.onchange = () => cb.closest(".arr-foto").classList.toggle("sai", cb.checked));
  L.querySelectorAll("[data-para]").forEach((inp) => inp.oninput = () => {
    const cb = L.querySelector(`[data-de="${CSS.escape(inp.dataset.para)}"]`); if (cb) cb.checked = !!inp.value.trim();
  });
  contarMarcados();
  L.onchange = contarMarcados;
}
function marcados() {
  const mover = [...document.querySelectorAll("#arr-lista [data-mover]:checked")].map((c) => c.dataset.mover);
  const renomear = [...document.querySelectorAll("#arr-lista [data-de]:checked")].map((c) => ({
    de: c.dataset.de, para: (document.querySelector(`#arr-lista [data-para="${CSS.escape(c.dataset.de)}"]`) || {}).value?.trim() || "",
  })).filter((x) => x.para && x.para !== x.de);
  return { mover, renomear };
}
function contarMarcados() {
  const m = marcados(), n = m.mover.length + m.renomear.length;
  const b = $("#bt-arr-aplicar");
  b.disabled = !n;
  b.textContent = !n ? "Nada marcado nesta parte" : m.mover.length ? `Tirar ${m.mover.length} repetida${m.mover.length > 1 ? "s" : ""}` : `Trocar ${m.renomear.length} nome${m.renomear.length > 1 ? "s" : ""}`;
}
$("#bt-arr-aplicar").onclick = async () => {
  const m = marcados(); if (!m.mover.length && !m.renomear.length) return;
  $("#bt-arr-aplicar").disabled = true;
  const r = await api("/api/arrumar/aplicar", m);
  aviso(r.erros && r.erros.length ? `Feito: ${r.feitos.length}. Não deu: ${r.erros.length} (veja na atividade)` : `Pronto: ${r.feitos.length} ${r.feitos.length === 1 ? "foto arrumada" : "fotos arrumadas"}`, 5000);
  acompanharArrumar();
};
