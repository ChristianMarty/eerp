class MessageBox extends HTMLElement {
    constructor() {
        super();

        let shadow = this.attachShadow({ mode: 'open' });
        shadow.innerHTML = `
            <style>
                @import "main.css";
            </style>
            <div class="messageBox" id="messageBox">
                <p id="messageBoxText"></p>
                <span id="messageBoxButton" style="margin: 0;  float: right">
                    <button class="buttonWarning" onclick="triggerClear()">Done</button>
                    <button class="buttonAction" onclick="print()">Print</button>
                </span>
            </div>
            
            <script>
        `;
        this.hide();
    }
    showError(message, duration) {
        this.shadowRoot.getElementById("messageBoxText").innerText = message;
        this.shadowRoot.getElementById("messageBox").classList.add('messageBoxError');
        this.shadowRoot.getElementById("messageBox").style.display = "block";
        this.shadowRoot.getElementById("messageBoxButton").style.display = "none";

        setTimeout(this.hide.bind(this), duration);
    }
    showSuccess(message, duration, print = null) {
        this.shadowRoot.getElementById("messageBoxText").innerText = message;
        this.shadowRoot.getElementById("messageBox").classList.add('messageBoxSuccess');
        this.shadowRoot.getElementById("messageBox").style.display = "block";
        if(print) {
            this.shadowRoot.getElementById("messageBoxButton").style.display = "block";
        }else{
            this.shadowRoot.getElementById("messageBoxButton").style.display = "none";
        }

        setTimeout(this.hide.bind(this), duration);
    }
    hide() {
        this.shadowRoot.getElementById("messageBoxButton").style.display = "none";
        this.shadowRoot.getElementById("messageBox").style.display = "none";
        this.shadowRoot.getElementById("messageBox").classList.remove('messageBoxError');
        this.shadowRoot.getElementById("messageBox").classList.remove('messageBoxSuccess');
        this.shadowRoot.getElementById("messageBoxText").innerText = "";
    }

}
customElements.define('message-box', MessageBox);
