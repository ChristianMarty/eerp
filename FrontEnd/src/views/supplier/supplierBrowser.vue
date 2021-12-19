<template>
  <div class="app-container">
    <template>
      <el-table :data="suppliers" style="width: 100%">
        <el-table-column prop="Name" label="Name" width="250" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/supplier/supplierView/' + row.Id" class="link-type">
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
import requestBN from '@/utils/requestBN'

export default {
  name: 'SupplierBrowser',
  components: {},
  data() {
    return {
      suppliers: null
    }
  },
  mounted() {
    this.getSuppliers()
  },
  methods: {
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    }
  }
}
</script>
