<template>
  <div class="app-container">
    <template>
      <el-checkbox v-model="supplier" @change="update()">Must be Supplier</el-checkbox>
      <el-checkbox v-model="manufacturer" @change="update()">Must be Manufacturer</el-checkbox>
      <el-checkbox v-model="contractor" @change="update()">Must be Contractor</el-checkbox>
      <el-checkbox v-model="carrier" @change="update()">Must be Carrier</el-checkbox>
      <el-checkbox v-model="customer" @change="update()">Must be Customer</el-checkbox>

      <el-table
        v-loading="loading"
        element-loading-text="Loading Vendors ..."
        element-loading-spinner="el-icon-loading"
        :data="vendors"
        style="width: 100%"
        row-key="Id"
        :tree-props="{ children: 'Children' }"
        default-expand-all
      >
        <el-table-column prop="FullName" label="Full Name" width="350" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/vendor/view/' + row.Id" class="link-type">
              <span>{{ row.FullName }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="ShortName" label="Short Name" width="250" sortable />
        <el-table-column prop="AbbreviatedName" label="Abbreviation" width="250" sortable />
        <el-table-column prop="AliasName" label="Alias"/>
      </el-table>
    </template>
  </div>
</template>

<script>
import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorList',
  components: {},
  data() {
    return {
      loading: true,
      vendors: [],
      supplier: false,
      manufacturer: false,
      contractor: false,
      carrier: false,
      customer: false
    }
  },
  mounted() {
    this.update()
  },
  methods: {
    update() {
      this.loading = true
      vendor.search(this.supplier, this.manufacturer, this.contractor, this.carrier, this.customer, true).then(response => {
        this.vendors = response
        this.loading = false
      })
    }
  }
}
</script>
