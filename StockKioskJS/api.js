import config from "./config.js";

export class Api {
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
        console.log(responseMessage);
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
    async removeStock(StockCode, Quantity, Note) {
        if (StockCode === "" || StockCode === null || Quantity === "" || Quantity === null) {
            return null;
        }

        const postData = {
            StockNumber: StockCode,
            RemoveQuantity: Quantity,
            Note: Note
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

// Private --------------------------------------------------------------
// Can't be declared as private because missing browser support!
    idempotency = "";
    encodeUrl(endpoint, params) {
        let auth = {
            'user': config.userName,
            'token': config.userToken
        };
        return config.apiUrl + endpoint + "?" + new URLSearchParams({...auth, ...params}).toString();
    }
}

