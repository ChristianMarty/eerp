import config from "./config.js";

function encodeUrl(endpoint, params) {
    let auth = {
        'user': config.userName,
        'token': config.userToken
    };
    return  config.apiUrl+endpoint+"?"+new URLSearchParams({ ...auth, ...params }).toString();
}
export async function getStockInformation(StockCode) {
    if(StockCode==="" || StockCode===null){
        return null;
    }

    const params = {
        'StockCode': StockCode
    };
    let response  = await fetch(encodeUrl("/stock/item", params));

    if (!response.ok) {
        return  null;
    }
    return response.json();
}

export async function countStock(StockCode, Quantity, Note) {
    if(StockCode==="" || StockCode===null || Quantity==="" || Quantity===null){
        return null;
    }

    const url = encodeUrl("/stock/history/item");
    const postData = {
        StockNumber: StockCode,
        Quantity: Quantity,
        Note: Note
    }

    let response  = await fetch(url, {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(postData)
    })

    if (!response.ok) {
        return  null;
    }
    return response.json();
}

export async function removeStock(StockCode, Quantity, Note) {
    if(StockCode==="" || StockCode===null || Quantity==="" || Quantity===null){
        return null;
    }

    const postData = {
        StockNumber: StockCode,
        RemoveQuantity: Quantity,
        Note: Note
    }
    let response  = await fetch(encodeUrl("/stock/history/item"), {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(postData)
    })

    if (!response.ok) {
        return  null;
    }
    return response.json();
}


