import config from "./config.js";

export class Api {
    constructor(user, token)  {
        this.user = user
        this.token = token
    }
    async getStockInformation(StockCode) {
        if (StockCode === "" || StockCode === null) {
            return null;
        }

        const params = {
            'StockCode': StockCode
        };
        let response = await fetch(this.encodeUrl("/stock/item", params));

        if (!response.ok) {
            return null;
        }
        const responseMessage = await response.json();
        this.idempotency = responseMessage.idempotency;
        return responseMessage;
    }

    async getWorkOrders() {
        const params = {
            'Status': 'InProgress'
        };
        let response = await fetch(this.encodeUrl("/workOrder", params));

        if (!response.ok) {
            return null;
        }
        const responseMessage = await response.json();
        this.idempotency = responseMessage.idempotency;
        return responseMessage;
    }

    async countStock(StockCode, Quantity, Note) {
        if (StockCode === "" || StockCode === null || Quantity === "" || Quantity === null) {
            return null;
        }

        const url = this.encodeUrl("/stock/history/item");
        const postData = {
            StockNumber: StockCode,
            Quantity: Quantity,
            Note: Note
        }

        let response = await fetch(url, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Idempotency-Key": this.idempotency
            },
            body: JSON.stringify(postData)
        })

        if (!response.ok) {
            return null;
        }
        const responseMessage = await response.json();
        this.idempotency = responseMessage.idempotency;
        return responseMessage;
    }
    async removeStock(StockCode, Quantity, WorkOrder, Note) {
        if (StockCode === "" || StockCode === null || Quantity === "" || Quantity === null) {
            return null;
        }
        if(WorkOrder === "null"){
            WorkOrder = null;
        }
        const postData = {
            StockNumber: StockCode,
            RemoveQuantity: Quantity,
            Note: Note,
            WorkOrderNumber: WorkOrder
        }
        let response = await fetch(this.encodeUrl("/stock/history/item"), {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Idempotency-Key": this.idempotency
            },
            body: JSON.stringify(postData)
        })

        if (!response.ok) {
            return null;
        }
        const responseMessage = await response.json();
        this.idempotency = responseMessage.idempotency;
        return responseMessage;
    }

    async printStockHistoryBon(StockHistoryCode) {
        if (StockHistoryCode === "" || StockHistoryCode === null) {
            return null;
        }

        const postData = {
            Data: [StockHistoryCode],
            PrinterId: config.bonPrinterId,
            RendererId: config.stockHistoryBonRendererId
        }
        let response = await fetch(this.encodeUrl("/peripheral/printer/print"), {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Idempotency-Key": this.idempotency
            },
            body: JSON.stringify(postData)
        })

        if (!response.ok) {
            return null;
        }
        const responseMessage = await response.json();
        this.idempotency = responseMessage.idempotency;
        return responseMessage;
    }

// Private --------------------------------------------------------------
// Can't be declared as private because missing browser support!
    idempotency = "";
    encodeUrl(endpoint, params) {
        let auth = {
            'user': this.user ,
            'token': this.token
        };
        return config.apiUrl + endpoint + "?" + new URLSearchParams({...auth, ...params}).toString();
    }
}
