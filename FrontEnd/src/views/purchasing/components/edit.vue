<template>
  <div class="edit-container">

    <el-form :inline="true" style="margin-top: 20px">
      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
        <el-button @click="addLine('Part')">Add Part</el-button>
        <el-button @click="addLine('Generic')">Add Generic Item</el-button>
      </el-form-item>
    </el-form>
    <el-table
      ref="itemTable"
      :key="tableKey"
      :data="lines"
      border
      style="width: 100%"
      :summary-method="calcSum"
      show-summary
    >
      <el-table-column prop="LineNo" label="Line" width="70" />

      <el-table-column label="Quantity" width="120">
        <template slot-scope="{ row }">
          <el-input-number
            v-model="row.QuantityOrderd"
            :controls="false"
            :min="1"
            :max="999999"
            style="width: 70pt"
          />
        </template>
      </el-table-column>

      <el-table-column label="SKU" width="220">
        <template slot-scope="{ row }">
          <el-input
            v-model="row.SupplierSku"
          />
        </template>
      </el-table-column>

      <el-table-column label="Item">
        <template slot-scope="{ row }">

          <template v-if="row.Type == 'Generic'">
            <el-input
              v-model="row.Description"
              placeholder="Description"
            />
          </template>

          <template v-if="row.Type == 'Part'">
            <el-row type="flex">

              <el-popover
                placement="right"
                width="400"
                trigger="click"
              >
                <el-select
                  v-model="row.MfrPartIndex"
                  placeholder="Manufacturer Part"
                  style="min-width: 300px; margin-right: 10px;"
                  @change="updaptePartLine(row)"
                >
                  <el-option
                    v-for="(item, index) in row.PartOptions"
                    :key="index"
                    :label="item.ManufacturerName +' - ' +item.ManufacturerPartNumber"
                    :value="index"
                  />
                </el-select>

                <el-input
                  slot="reference"
                  v-model="row.PartNo"
                  placeholder="PartNo"
                  style="width: 150px; margin-right: 10px;"
                  @change="getPartData(row)"
                />
              </el-popover>

              <el-select
                v-model="row.ManufacturerName"
                placeholder="Manufacturer"
                filterable
                style="min-width: 200px; margin-right: 10px;"
              >
                <el-option
                  v-for="item in partManufacturer"
                  :key="item.Name"
                  :label="item.Name"
                  :value="item.Name"
                />
              </el-select>

              <el-input
                v-model="row.ManufacturerPartNumber"
                placeholder="Part Number"
                style="min-width: 250px; max-width: 250px; margin-right: 10px;"
              />

              <el-input
                v-model="row.Description"
                placeholder="Description"
              />
            </el-row>
          </template>

        </template>
      </el-table-column>

      <el-table-column prop="Price" label="Price" width="120">
        <template slot-scope="{ row }">
          <el-input-number
            v-model="row.Price"
            :controls="false"
            :precision="4"
            :min="0.0000"
            :max="999999"
            style="width: 70pt"
          />
        </template>
      </el-table-column>
      </el-table-column>
      <el-table-column label="Total" width="120">
        <template slot-scope="{ row }">
          <span>{{ (Math.round((row.QuantityOrderd*row.Price) * 100000)/100000) }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'PurchaseOrderEdit',
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      orderData: this.$props.orderData,
      lines: null,
      tableKey: 0,
      line: 0,
      orderStatus: 0,
      suppliers: null
    }
  },
  mounted() {
    this.getSuppliers()
    this.getManufacturers()
    this.getOrderLines()
  },
  methods: {
    save() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/edit',
        data: { data: { Lines: this.lines, PoNo: this.$props.orderData.PoNo }}
      }).then(response => {
        if (response.error == null) {
          this.getOrderLines()
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    },
    updateTable() {
      this.tableKey += 1
      this.$forceUpdate()
    },
    addLine(lineType) {
      this.line++
      const row = {
        OrderLineId: 0,
        LineNo: this.line,
        QuantityOrderd: 1,
        SupplierSku: null,
        Description: '',
        Price: 0,
        Type: lineType,

        PartNo: null,
        ManufacturerName: null,
        ManufacturerPartNumber: '',
        Description: null,

        MfrPartIndex: null,
        PartOptions: null
      }

      this.lines.push(row)
    },
    calcSum(param) {
      let total = 0
      this.lines.forEach(element => {
        const line = element.QuantityOrderd * element.Price
        total += line
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[5] = Math.round(total * 100000) / 100000
      return totalLine
    },
    getOrder() {
      requestBN({
        url: '/purchasOrder',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.data.orderNumber
        }
      }).then(response => {
        this.orderData = response.data[0]
      })
    },
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
        this.line = this.lines.length
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.partManufacturer = response.data
      })
    },
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    updaptePartLine(row) {
      const index = eval(row.LineNo - 1)
      const partIndex = eval(row.MfrPartIndex)
      this.lines[index].ManufacturerName =
        row.PartOptions[partIndex].ManufacturerName
      this.lines[index].ManufacturerPartNumber =
        row.PartOptions[partIndex].ManufacturerPartNumber

      this.lines[index].PartOptions = null
    },
    getPartData(row) {
      const index = eval(row.LineNo - 1)
      requestBN({
        url: '/productionPart/item',
        methood: 'get',
        params: { PartNo: row.PartNo }
      }).then(response => {
        this.lines[index].PartOptions =
          response.data.ManufacturerPart
        this.lines[index].Description = this.lines[
          index
        ].PartOptions[0].Description
      })
    }
  }
}
</script>
