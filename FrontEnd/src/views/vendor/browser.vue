<template>
  <div class="app-container">
    <template>
      <el-checkbox v-model="supplier" @change="update()">Must be Supplier</el-checkbox>
      <el-checkbox v-model="manufacturer" @change="update()">Must be Manufacturer</el-checkbox>
      <el-table :data="vendors" style="width: 100%">
        <el-table-column prop="Name" label="Name" width="250" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/vendor/view/' + row.Id" class="link-type">
              <span>{{ row.Name }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Url" label="Url" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorBrowser',
  components: {},
  data() {
    return {
      vendors: [],
      supplier: false,
      manufacturer: false
    }
  },
  async mounted() {
    this.vendors = await vendor.search(this.supplier, this.manufacturer)
  },
  methods: {
    async update() {
      this.vendors = await vendor.search(this.supplier, this.manufacturer)
    }
  }
}
</script>
