        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('progress').style.width = scrolled + '%';
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show-cinematic');
                } else {
                    entry.target.classList.remove('show-cinematic');
                }
            });
        }, { threshold: 0.05, rootMargin: "0px 0px -20px 0px" });

        document.querySelectorAll('.hidden-cinematic').forEach((el) => observer.observe(el));

        const blob = document.getElementById("blob");
        document.body.onpointermove = event => {
            const { clientX, clientY } = event;
            blob.animate({
                left: `${clientX}px`,
                top: `${clientY}px`
            }, { duration: 2000, fill: "forwards" });
        };

(function(){
  const target=new Date('2026-09-11T20:00:00');
  const ids=['cdDays','cdHours','cdMins','cdSecs'];
  let prev=[];
  function tick(){
    const diff=target-new Date();
    if(diff<=0){ids.forEach(id=>{document.getElementById(id).textContent='00';});return;}
    const vals=[
      Math.floor(diff/864e5),
      Math.floor((diff%864e5)/36e5),
      Math.floor((diff%36e5)/6e4),
      Math.floor((diff%6e4)/1e3)
    ];
    ids.forEach((id,i)=>{
      const el=document.getElementById(id);
      const s=String(vals[i]).padStart(2,'0');
      if(s!==prev[i]){
        el.classList.add('flip');
        setTimeout(()=>{el.textContent=s;el.classList.remove('flip');},80);
        prev[i]=s;
      }
    });
  }
  tick();
  setInterval(tick,1000);
})();