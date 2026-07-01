</div></div>
<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
}
function showToast(msg){
    const t=document.getElementById('rz-toast');
    t.textContent=msg;t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),2500);
}
function copyText(txt){
    navigator.clipboard.writeText(txt).then(()=>showToast('✅ Copied!'));
}
particlesJS('particles-js',{
    particles:{number:{value:50,density:{enable:true,value_area:900}},
    color:{value:['#7c3aed','#06b6d4','#a855f7']},
    shape:{type:'circle'},opacity:{value:0.25,random:true},
    size:{value:2,random:true},
    line_linked:{enable:true,distance:130,color:'#7c3aed',opacity:0.08},
    move:{enable:true,speed:0.8}},
    interactivity:{events:{onhover:{enable:true,mode:'grab'}},
    modes:{grab:{distance:140,line_linked:{opacity:0.2}}}}
});
</script>
</body>
</html>
