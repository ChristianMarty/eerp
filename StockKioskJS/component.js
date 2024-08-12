
function partInformation(stockData){
    return `
        <table>
        <tr>
          <th>Manufacturer:</th>
          <td>${stockData.Part.ManufacturerName}</td>
        
        </tr>
        <tr>
          <th>Part Number:</th>
          <td>${stockData.Part.ManufacturerPartNumber}</td>
        </tr>
        </table>
`;
}
function locationInformation(stockData){
    return `
      <table>
        <tr>
          <th>Location:</th>
          <td>${stockData.Location.Name}</td>
        </tr>
        <tr>
          <th>Location Code:</th>
          <td>${stockData.Location.ItemCode}</td>
        </tr>
        <tr>
          <th>Location Path:</th>
          <td>${stockData.Location.Path}</td>
        </tr>
      </table>
`;
}
function stockInformation(stockData){
    return `
      <table>
        <tr>
          <th>Quantity:</th>
          <td>${stockData.Quantity.Quantity}</td>
        </tr>
        <tr>
          <th>Create Quantity:</th>
          <td>${stockData.Quantity.CreateQuantity}</td>
        </tr>
        <tr>
          <th>Days Since Stocktaking:</th>
          <td>${stockData.Quantity.Certainty.DaysSinceStocktaking}</td>
        </tr>
      </table>
`;
}
