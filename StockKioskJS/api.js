import config from "./config.js";
export async function getStockInformation(StockCode) {
    if(StockCode==="" || StockCode===null){
        return null;
    }

    var params = {
        'user': config.userName,
        'token': config.userToken,
        'StockCode': StockCode
    };

    const url = config.apiUrl+"/stock/item?"+new URLSearchParams(params).toString();
    let response  = await fetch(url);

    if (!response.ok) {
        return  null;
    }
    return await response.json();
}

export async function countStock(StockCode, Quantity, Note) {
    if(StockCode==="" || StockCode===null){
        return null;
    }
    if(Quantity==="" || Quantity===null){
        return null;
    }

    const url = config.apiUrl+"/stock/history/item"
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
    return await response.json();
}

export async function removeStock(StockCode, Quantity, Note) {
    if(StockCode==="" || StockCode===null){
        return null;
    }
    if(Quantity==="" || Quantity===null){
        return null;
    }

    const url = config.apiUrl+"/stock/history/item"
    const postData = {
        StockNumber: StockCode,
        RemoveQuantity: Quantity,
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
    return await response.json();
}


