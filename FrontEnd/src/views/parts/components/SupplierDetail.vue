<template>
  <div class="supplier-container">
    <aside><b>{{ data.Name }}</b>
      <el-row :gutter="20">
        <el-col :span="7">
          <span style="margin-top:50px">Last Price: {{ data.PaidPrice }}</span><br>
          <span style="margin-top:50px">Part No: <a :href="data.SupplierPartLink" target="blank">{{ data.SupplierPartNo }}</a></span><br>
          <span style="margin-top:50px">Note: {{ data.Note }}</span><br>
        </el-col>

        <el-col :span="8" style="min-height: 100%">
          <el-card class="box-card" style="width: 100%">
            <span><b>Availability</b></span><br>
            <el-divider />
            <span>{{ data.SupplierPartData.SearchResults.Parts[0].Availability }}</span><br>
            <span>{{ data.SupplierPartData.SearchResults.Parts[0].LeadTime }} Lead Time</span><br>
            <span>Minimum Order Quantity: {{ data.SupplierPartData.SearchResults.Parts[0].Min }}</span><br>
            <span>Order Quantity Multiplier: {{ data.SupplierPartData.SearchResults.Parts[0].Mult }}</span><br>
          </el-card>
        </el-col>

        <el-col :span="9">
          <el-card class="box-card" style="width: 100%">
            <span><b>Price</b></span>

            <el-table
              :data="data.SupplierPartData.SearchResults.Parts[0].PriceBreaks"
              border
              style="width: 100%; margin-top:10px"
            >
              <el-table-column
                prop="Quantity"
                label="Quantity"
              />
              <el-table-column
                prop="Price"
                label="Price"
              />
            </el-table>
          </el-card>
        </el-col>
      </el-row>
    </aside>

  </div>
</template>

<script>

export default {
  props: { data: { type: Object, default: null }},
  data() {
    return {
    }
  },
  mounted() {

  },
  methods: {
  }
}
</script>
