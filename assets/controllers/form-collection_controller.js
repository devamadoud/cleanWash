import { Controller } from "@hotwired/stimulus";

export default class extends Controller {

    static values = {
        addLabel: String,
        deleteLabel: String,
        formType: String,
    }
    /**
     * Initializes the controller and sets up the UI elements.
     *
     * This function is called when the controller is connected to the DOM.
     * It sets the initial index based on the number of child elements in the element.
     * It adds a CSS class to the element to define the grid layout.
     * It creates a button element and sets its attributes and content.
     * It adds a click event listener to the button that calls the addElement function.
     * It iterates over each child node of the element and calls the addDeletteBtn function.
     * Finally, it appends the button element to the element.
     */
    connect() {
        this.index = this.element.childElementCount;

        if(this.formTypeValue === 'addable'){
            const btn = document.createElement('button');

            btn.setAttribute('type', 'button');
            btn.setAttribute('class', 'inline-flex items-center justify-center px-3 py-2 text-base font-medium text-center text-white rounded-lg bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 dark:focus:ring-yellow-900"');
            btn.innerHTML = this.addLabelValue || 'Ajouter un vêtement';

            btn.addEventListener('click', this.addElement);

            this.element.childNodes.forEach(this.addDeletteBtn);
            this.element.append(btn);
            if (this.element.childElementCount === 1) {
                this.addElement();
            }
        }else{
            this.addElement();
        }


        const inputs = document.getElementsByClassName('form-collection-type');
        if (!inputs) {
            console.error('No elements with class "form-collection-type" found');
            return;
        }

        Array.from(inputs).forEach((input) => {
            if (!input || !input.childNodes || !input.childNodes[0]) {
                console.error('Invalid input element');
                return;
            }

            const childNode = input.childNodes[0];
            if (!childNode || !childNode.name || !childNode.id) {
                console.error('Invalid input element properties');
                return;
            }

            input.parentNode.setAttribute('class', 'flex gap-4 flex-4');

            const parentNode = input.parentNode.parentNode;
            if (parentNode) {
                parentNode.setAttribute('class', 'gap-4 my-4 flex place-content-center');
            } else {
                console.error('Invalid parent node');
                return;
            }

            childNode.name = childNode.name.replace('__name__', this.index);
            childNode.id = childNode.id.replace('__name__', this.index);
        })


    }

    /**
    * @param {MouseEvent} e
    */
    addElement = (e) => {
        if (e) e.preventDefault();

        const elemnet = document.createRange().createContextualFragment(
            this.element.dataset['prototype'].replace('__name__', this.index),
        ).firstElementChild;

        if(this.formTypeValue === 'addable'){
            this.addDeletteBtn(elemnet);
            this.index++
            this.element.append(elemnet);
        }

        const inputs = document.getElementsByClassName('form-collection-type');
        if (!inputs) {
            console.error('No elements with class "form-collection-type" found');
            return;
        }
        
        Array.from(inputs).forEach((input) => {
            if (!input || !input.childNodes || !input.childNodes[0]) {
                console.error('Invalid input element');
                return;
            }

            const childNode = input.childNodes[0];
            if (!childNode || !childNode.name || !childNode.id) {
                console.error('Invalid input element properties');
                return;
            }

            input.parentNode.setAttribute('class', 'flex gap-4 flex-4');

            const parentNode = input.parentNode.parentNode;
            if (parentNode) {
                parentNode.setAttribute('class', 'gap-4 my-4 flex place-content-center');
            } else {
                console.error('Invalid parent node');
                return;
            }

            childNode.name = childNode.name.replace('__name__', this.index);
            childNode.id = childNode.id.replace('__name__', this.index);
        })

        if (this.element.childElementCount >= 1) {
            const lastElement = this.element.childNodes[this.element.childElementCount - 2];
            lastElement.insertAdjacentElement('beforebegin', elemnet);
            console.log(lastElement);
        } else {
            this.element.insertAdjacentElement('beforebegin', elemnet);
        }
    }

    /**
    * @param {HTMLelement} item
    */
    addDeletteBtn = (item) => {
        const dbtn = document.createElement('button');
        dbtn.setAttribute('type', 'button');
        dbtn.setAttribute('class', 'text-red-500 hover:text-red-700 mt-2 inline-flex px-3 py-2 text-sm font-medium rounded-md text-red-600 bg-white hover:text-white hover:bg-red-600 border border-red-600');
        dbtn.innerHTML = this.deleteLabelValue || 'Supprimer';

        item.append(dbtn);

        dbtn.addEventListener('click', (e) => {
            e.preventDefault();
            item.remove();
        })
    }
}