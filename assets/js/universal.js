const canDoIt = document.getElementById('form-canDoIt');
if(canDoIt) {
    const canDoItChoice1 = document.getElementById('form_waiteForAgentChoice_0');
    const canDoItChoice2 = document.getElementById('form_waiteForAgentChoice_1');
    const collectChoice1 = document.getElementById('form_collecteChoice_0');
    const collectChoice2 = document.getElementById('form_collecteChoice_1');

    canDoItChoice2.addEventListener('change', (e) => {

        if(e.target.checked === true){
            canDoIt.classList.replace('hidden', 'flex');
            collectChoice1.required = false;
            collectChoice2.required = false;
        }
    })

    canDoItChoice1.addEventListener('change', (e) => {

        if(e.target.checked === true){
            canDoIt.classList.replace('flex', 'hidden');
            collectChoice1.required = false;
            collectChoice2.required = false;
        }
    })
}