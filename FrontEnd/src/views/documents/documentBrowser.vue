<template>
  <div class="app-container">
    <template>
      <el-table :data="documents" style="width: 100%">
        <el-table-column
          prop="Description"
          label="Description"
          width="400"
          sortable
        />
        <el-table-column prop="Type" label="Type" width="100" sortable />
        <el-table-column prop="Path" label="Path">
          <template slot-scope="{ row }">
            <a :href="row.Path" target="blank">
              <el-button icon="el-icon-document">
                Open in new tab
              </el-button>
            </a>
          </template>
        </el-table-column>
        <el-table-column
          prop="CreationDate"
          label="Creation Date"
          width="170"
          sortable
        />
        <el-table-column prop="Hash" label="MD5 Hash" width="300" />
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'DocumentBrowser',
  components: {},
  data() {
    return {
      documents: null
    }
  },
  mounted() {
    this.getDocuments()
  },
  methods: {
    getDocuments() {
      requestBN({
        url: '/document',
        methood: 'get'
      }).then(response => {
        this.documents = response.data
      })
    }
  }
}
</script>
