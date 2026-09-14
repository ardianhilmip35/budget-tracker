
import React, { useMemo, useState } from 'react'
import { createRoot } from 'react-dom/client'
import * as XLSX from 'xlsx'
import './styles.css'

const categories=['Kos','Operasional Jakarta','Allianz Jiwa/CI','Dana Darurat','Saham BMRI','Tabungan Emas','Uang Bebas']
const defaultProfiles={
 A:{label:'Opsi A',thp:8500000,budgets:{'Kos':1300000,'Operasional Jakarta':2700000,'Allianz Jiwa/CI':1300000,'Dana Darurat':850000,'Saham BMRI':1000000,'Tabungan Emas':1250000,'Uang Bebas':100000}},
 B:{label:'Opsi B',thp:9000000,budgets:{'Kos':1300000,'Operasional Jakarta':2700000,'Allianz Jiwa/CI':1300000,'Dana Darurat':900000,'Saham BMRI':1100000,'Tabungan Emas':1500000,'Uang Bebas':200000}}
}
const KEY='budget-tracker-jakarta-v1'
const fallback={activeProfile:'B',cycleMonth:new Date().toISOString().slice(0,7),profiles:defaultProfiles,transactions:[]}
function load(){try{return {...fallback,...JSON.parse(localStorage.getItem(KEY)||'null')}}catch{return fallback}}
function save(s){localStorage.setItem(KEY,JSON.stringify(s))}
function idr(n){return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(n||0))}
function parts(m){const [y,mo]=m.split('-').map(Number);return {y,mo}}
function start(m){const {y,mo}=parts(m);return new Date(Date.UTC(y,mo-1,15))}
function next(m){const {y,mo}=parts(m);return new Date(Date.UTC(y,mo,15))}
function end(m){const d=next(m);d.setUTCDate(d.getUTCDate()-1);return d}
function ymd(d){return d.toISOString().slice(0,10)}
function dateLabel(s){return new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'long',year:'numeric',timeZone:'UTC'}).format(new Date(s+'T00:00:00Z'))}
function inCycle(date,m){return date>=ymd(start(m))&&date<ymd(next(m))}
function monthLabel(m){const {y,mo}=parts(m);return new Intl.DateTimeFormat('id-ID',{month:'long',year:'numeric',timeZone:'UTC'}).format(new Date(Date.UTC(y,mo-1,1)))}
function historyRows(state){
 const months=[...new Set(state.transactions.map(t=>{const d=new Date(t.date+'T00:00:00Z'),y=d.getUTCFullYear(),mo=d.getUTCMonth()+1,day=d.getUTCDate();const x=day>=15?new Date(Date.UTC(y,mo-1,1)):new Date(Date.UTC(y,mo-2,1));return `${x.getUTCFullYear()}-${String(x.getUTCMonth()+1).padStart(2,'0')}`}))].sort().reverse()
 return months.map(m=>{const sums=Object.fromEntries(categories.map(c=>[c,0]));state.transactions.filter(t=>inCycle(t.date,m)).forEach(t=>sums[t.category]=(sums[t.category]||0)+Number(t.amount));return {Bulan:monthLabel(m),Kos:sums['Kos'],Operasional:sums['Operasional Jakarta'],Allianz:sums['Allianz Jiwa/CI'],Darurat:sums['Dana Darurat'],BMRI:sums['Saham BMRI'],Emas:sums['Tabungan Emas'],'Uang Bebas':sums['Uang Bebas'],Total:Object.values(sums).reduce((a,b)=>a+b,0)}})
}
function App(){
 const [state,setState]=useState(load)
 const [form,setForm]=useState({date:ymd(start(load().cycleMonth)),category:'Operasional Jakarta',description:'',amount:'',method:'QRIS'})
 const update=(fn)=>setState(p=>{const n=typeof fn==='function'?fn(p):fn;save(n);return n})
 const profile=state.profiles[state.activeProfile]
 const txs=useMemo(()=>state.transactions.filter(t=>inCycle(t.date,state.cycleMonth)),[state.transactions,state.cycleMonth])
 const sums=useMemo(()=>{const r=Object.fromEntries(categories.map(c=>[c,0]));txs.forEach(t=>r[t.category]+=Number(t.amount));return r},[txs])
 const totalBudget=Object.values(profile.budgets).reduce((a,b)=>a+b,0), totalSpent=Object.values(sums).reduce((a,b)=>a+b,0)
 const history=useMemo(()=>historyRows(state),[state])
 const submit=e=>{e.preventDefault();const amount=Number(form.amount);if(!form.date||!amount||amount<=0)return;update(p=>({...p,transactions:[{id:crypto.randomUUID(),...form,amount},...p.transactions]}));setForm(f=>({...f,description:'',amount:''}))}
 const del=id=>{if(confirm('Hapus transaksi ini?'))update(p=>({...p,transactions:p.transactions.filter(t=>t.id!==id)}))}
 const exportCycle=()=>{const summary=categories.map(c=>({Kategori:c,Budget:profile.budgets[c],Realisasi:sums[c],Sisa:profile.budgets[c]-sums[c]}));const detail=txs.map(t=>({Tanggal:t.date,Kategori:t.category,Keterangan:t.description,Nominal:t.amount,Metode:t.method}));const wb=XLSX.utils.book_new();XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(summary),'Ringkasan');XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(detail),'Transaksi');XLSX.writeFile(wb,`budget-${state.cycleMonth}.xlsx`)}
 const exportAll=()=>{const wb=XLSX.utils.book_new();XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(history),'Riwayat Bulanan');XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(state.transactions),'Semua Transaksi');XLSX.writeFile(wb,'riwayat-budget.xlsx')}
 return <div className="app-shell">
  <header className="topbar"><div><h1>Budget Tracker Jakarta</h1><p>Siklus {dateLabel(ymd(start(state.cycleMonth)))} – {dateLabel(ymd(end(state.cycleMonth)))}</p></div><div className="header-actions"><button onClick={exportCycle}>Download Excel Bulan Ini</button><button className="secondary" onClick={exportAll}>Download Semua Riwayat</button></div></header>
  <section className="controls card">
   <label>Opsi budget<select value={state.activeProfile} onChange={e=>update({...state,activeProfile:e.target.value})}><option value="A">Opsi A — THP {idr(state.profiles.A.thp)}</option><option value="B">Opsi B — THP {idr(state.profiles.B.thp)}</option></select></label>
   <label>Bulan siklus<input type="month" value={state.cycleMonth} onChange={e=>{const m=e.target.value;update({...state,cycleMonth:m});setForm(f=>({...f,date:ymd(start(m))}))}}/></label>
   <div className="metric"><span>Total budget</span><strong>{idr(totalBudget)}</strong></div><div className="metric"><span>Total realisasi</span><strong>{idr(totalSpent)}</strong></div>
  </section>
  <section className="budget-grid">{categories.map(c=>{const b=profile.budgets[c],s=sums[c],r=b-s,p=b?Math.min(100,Math.round(s/b*100)):0;return <article className="budget-card" key={c}><div className="budget-card-head"><h3>{c}</h3><span>{p}%</span></div><div className="progress"><span style={{width:p+'%'}}/></div><dl><div><dt>Budget</dt><dd>{idr(b)}</dd></div><div><dt>Realisasi</dt><dd>{idr(s)}</dd></div><div><dt>Sisa</dt><dd className={r<0?'danger':''}>{idr(r)}</dd></div></dl></article>})}</section>
  <section className="two-col">
   <form className="card transaction-form" onSubmit={submit}><h2>Tambah Transaksi</h2>
    <label>Tanggal<input type="date" min={ymd(start(state.cycleMonth))} max={ymd(end(state.cycleMonth))} value={form.date} onChange={e=>setForm({...form,date:e.target.value})}/></label>
    <label>Kategori<select value={form.category} onChange={e=>setForm({...form,category:e.target.value})}>{categories.map(c=><option key={c}>{c}</option>)}</select></label>
    <label>Keterangan<input value={form.description} onChange={e=>setForm({...form,description:e.target.value})} placeholder="Contoh: makan malam"/></label>
    <label>Nominal<input type="number" min="1" inputMode="numeric" value={form.amount} onChange={e=>setForm({...form,amount:e.target.value})} placeholder="35000"/></label>
    <label>Metode<select value={form.method} onChange={e=>setForm({...form,method:e.target.value})}>{['Cash','QRIS','Transfer','Debit','E-Wallet'].map(m=><option key={m}>{m}</option>)}</select></label>
    <button type="submit">Simpan Transaksi</button>
   </form>
   <div className="card"><h2>Transaksi Siklus Aktif</h2><div className="table-wrap"><table><thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th></th></tr></thead><tbody>{txs.length===0?<tr><td colSpan="5" className="empty">Belum ada transaksi.</td></tr>:txs.map(t=><tr key={t.id}><td>{t.date}</td><td>{t.category}</td><td>{t.description||'-'}</td><td>{idr(t.amount)}</td><td><button className="danger-btn" onClick={()=>del(t.id)}>Hapus</button></td></tr>)}</tbody></table></div></div>
  </section>
  <section className="card history"><h2>Riwayat Bulanan</h2><div className="table-wrap"><table><thead><tr><th>Siklus</th><th>Kos</th><th>Operasional</th><th>Allianz</th><th>Darurat</th><th>BMRI</th><th>Emas</th><th>Uang Bebas</th><th>Total</th></tr></thead><tbody>{history.map(r=><tr key={r.Bulan}><td>{r.Bulan}</td><td>{idr(r.Kos)}</td><td>{idr(r.Operasional)}</td><td>{idr(r.Allianz)}</td><td>{idr(r.Darurat)}</td><td>{idr(r.BMRI)}</td><td>{idr(r.Emas)}</td><td>{idr(r['Uang Bebas'])}</td><td>{idr(r.Total)}</td></tr>)}</tbody></table></div></section>
  <footer>Data tersimpan di browser perangkat ini. Export Excel secara berkala sebagai backup.</footer>
 </div>
}
createRoot(document.getElementById('root')).render(<App/>)
