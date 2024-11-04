import eerpApi from './apiQuery'

export class StockItem {
  ItemCode: string = ""
  StockNumber: string = ""
  LotNumber: string = ""
  DateCode: string = ""
  Date: string = ""
  Description: string = ""
  Deleted: boolean = false

  Supplier = class {
    Name: string = ""
    PartNumber: string = ""
    VendorId: number = 0
  }
  Purchase = class {
    PurchaseOrderNumber: number = 0
    LineNumber: number = 0
    Price: number = 0
    Discount: number = 0
    CurrencyCode: string = ""
    PurchaseDate: string = ""
    OrderReference: string = ""
    ProductionPartNumber: string = ""
    Quantity: number = 0
    Description: string = ""
    SupplierId: number = 0
    SupplierName: string = ""
    ItemCode: string = ""
    PriceAfterDiscount: string = ""
  }
  Part = class {
    ManufacturerName: string = ""
    ManufacturerId: number = 0
    ManufacturerPartNumber: string = ""
    ManufacturerPartNumberId: number = 0
    ManufacturerPartItemId: null|number = null
    SpecificationPartRevisionId: null|number = null
  }
  Quantity = class {
    Quantity: number = 0
    CreateQuantity: number = 0
    CreateData: string = ""
  }
  Location = class {
    LocationNumber: number = 0
    ItemCode: string = ""
    Name: string = ""
    Path: string = ""
    HomeName: string = ""
    HomePath: string = ""
  }
}
export class RemoveStockItem {
  ItemCode: string = ""
  Note: string = ""
  RemoveQuantity: number = 0

}

export class BulkRemoveResult {
  ItemCode: string = ""
  ManufacturerName: string = ""
  ManufacturerPartNumber: string = ""
  Note: string = ""
  RemoveQuantity: number = 0

}

export class StockCountResult {

}

export default class Stock  {
  async get(StockCode: string): Promise<StockItem|null> {
    const result = await new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/stock/item',
        params: { StockCode: StockCode }
      }).then(response  => {

        if (response.error === null) {
          resolve(response)
        } else {
          reject(response.error)
        }
      })
    })

    return result.data;
  }

  async count(StockNumber: string, Quantity: number, Note : string|null) : Promise<StockCountResult|null> {
    const result = await new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/stock/history/item',
        data: {
          StockNumber: StockNumber,
          Quantity: Quantity,
          Note: Note
        }
      }).then(response  => {
        if (response.error === null) {
          resolve(response)
        } else {
          reject(response.error)
        }
      })
    })
    return result.data;
  }

  async bulkRemove(StockItems: RemoveStockItem[], WorkOrderNumber: string|null = null): Promise<BulkRemoveResult[]> {
    const data = {
      Items: StockItems,
      WorkOrderNumber: WorkOrderNumber
    }
    const result = await new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/stock/history/bulkRemove',
        data: data
      }).then(response => {
        if (response.error === null) {
          resolve(response)
        } else {
          reject(response.error)
        }
      })
    })
    return result.data;
  }
}
