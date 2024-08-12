class MessageBox extends HTMLElement {
    constructor() {
        super();

        const shadow = this.attachShadow({ mode: 'open' });
        shadow.innerHTML = `
            <style>
                @import "main.css";
            </style>
            <div class="messageBox" id="messageBox">
                <p id="messageBoxText"></p>
            </div>`;
        this.hide();
    }
    showError(message, duration) {
        this.shadowRoot.getElementById("messageBoxText").innerText = message;
        this.shadowRoot.getElementById("messageBox").classList.add('messageBoxError');
        this.shadowRoot.getElementById("messageBox").style.display = "block";

        setTimeout(this.hide.bind(this), duration);
    }
    showSuccess(message, duration) {
        this.shadowRoot.getElementById("messageBoxText").innerText = message;
        this.shadowRoot.getElementById("messageBox").classList.add('messageBoxSuccess');
        this.shadowRoot.getElementById("messageBox").style.display = "block";

        setTimeout(this.hide.bind(this), duration);
    }
    hide() {
        this.shadowRoot.getElementById("messageBox").style.display = "none";
        this.shadowRoot.getElementById("messageBox").classList.remove('messageBoxError');
        this.shadowRoot.getElementById("messageBox").classList.remove('messageBoxSuccess');
        this.shadowRoot.getElementById("messageBoxText").innerText = "";
    }
}
customElements.define('message-box', MessageBox);
