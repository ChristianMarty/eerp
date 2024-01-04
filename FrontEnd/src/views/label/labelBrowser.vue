<template>
  <div class="app-container">
    <el-table :data="partData" border style="width: 100%">
      <el-table-column prop="Name" sortable label="Name" width="220">
        <template slot-scope="{ row }">
          <router-link :to="'/label/' + row.Id" class="link-type">
            <span>{{ row.Name }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Tag" label="Tags" sortable width="240" />
      <el-table-column prop="Render" label="Render" sortable width="120" />
      <el-table-column prop="Language" label="Language" sortable width="120" />
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      partData: null
    }
  },
  mounted() {
    this.getPartData()
  },
  methods: {
    getPartData() {
      requestBN({
        url: '/label',
        methood: 'get'
      }).then(response => {
        this.partData = response.data
      })
    }
  }
}
</script>
