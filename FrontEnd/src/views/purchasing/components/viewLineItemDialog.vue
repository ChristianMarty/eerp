<template>
  <div class="view-line-item-dialog">
    <el-dialog
      :title="'Line '+line.LineNo"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="onOpen()"
    >

      <el-descriptions title="Details:" :column="2">
        <el-descriptions-item label-class-name="my-label" label="Type"> {{ line.LineType }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Price">{{ line.Price }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Quantity">{{ line.QuantityOrderd }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Unit">{{ line.UnitOfMeasurement }}</el-descriptions-item>

        <el-descriptions-item label-class-name="my-label" label="VAT">{{ line.VatValue }}%</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Discount">{{ line.Discount }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Total (excl. vat)">{{ line.Total }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Total">{{ line.FullTotal }}</el-descriptions-item>
      </el-descriptions>
      <p />
      <el-descriptions title="Part:" :column="2">
        <el-descriptions-item label-class-name="my-label" label="Manufacturer ">{{ line.ManufacturerName }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Sku ">{{ line.SupplierSku }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Part Number">
          <template v-if="line.ManufacturerPartId">
            <router-link
              :to="'/mfrParts/partView/' + line.ManufacturerPartId"
              class="link-type"
            >
              <span>{{ line.ManufacturerPartNumber }}</span>
            </router-link>
          </template>
          <template v-else>
            {{ line.ManufacturerPartNumber }}
          </template>
        </el-descriptions-item>

        <el-descriptions-item label-class-name="my-label" label="Order Reference">{{ line.OrderReference }}</el-descriptions-item>
        <el-descriptions-item label-class-name="my-label" label="Description">{{ line.Description }}</el-descriptions-item>
      </el-descriptions>
      <p />
      <el-descriptions title="Note:" />
      {{ line.Note }}

      <el-descriptions title="Track:" />
      <p><b>Stock Part:</b> {{ line.StockPart }}</p>
      <el-table
        ref="itemTable"
        :data="trackData"
        border
        style="width: 100%"
        :cell-style="{ padding: '0', height: '30px' }"
      >
        <el-table-column prop="Type" label="Type" width="120" sortable />

        <el-table-column label="Reference" sortable>
          <template slot-scope="{ row }">
            <template v-if="row.Type == 'Part Stock'">
              <router-link :to="'/stock/item/' + row.Barcode" class="link-type">
                <span>{{ row.Barcode }} </span>
              </router-link>
              <span style="float: right;"> Original Quantity: {{ row.CreateQuantity }} </span>
            </template>
            <template v-if="row.Type == 'Inventory'">
              <router-link
                :to="'/inventory/inventoryView/' + row.Barcode"
                class="link-type"
              >
                <span>{{ row.Barcode }} </span>
              </router-link>
              <span style="float: right;"> {{ row.Description }} </span>
            </template>
          </template>
        </el-table-column>
      </el-table>

      <span slot="footer" class="dialog-footer">
        <el-button @click="closeDialog()">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ViewLineItemDialog',
  props: {
    visible: { type: Boolean, default: false },
    line: { type: Object, default: null }
  },
  data() {
    return {
      trackData: null
    }
  },
  mounted() {
  },
  methods: {
    onOpen() {
      this.getTrackData()
    },
    getTrackData() {
      requestBN({
        url: '/purchasing/item/track',
        methood: 'get',
        params: {
          ReceivalId: this.$props.line.ReceivalId
        }
      }).then(response => {
        this.trackData = response.data
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>
<style>
  .my-label {
    font-weight: bold;
    color: black;
  }
</style>
