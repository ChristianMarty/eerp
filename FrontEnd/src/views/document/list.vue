<template>
  <div class="app-container">
    <template>
      <el-table :data="documents" style="width: 100%">
        <el-table-column
          prop="ItemCode"
          label="Item Code"
          width="120"
          sortable
        >
          <template slot-scope="{ row }">
            <router-link :to="'/document/item/' + row.DocumentNumber" class="link-type">
              <span> {{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column
          prop="Name"
          label="Name"
          width="400"
          sortable
        />
        <el-table-column prop="Type" label="Type" width="100" sortable />
        <el-table-column prop="Path" label="Path" width="200">
          <template slot-scope="{ row }">
            <a :href="row.Path" target="blank">
              <el-button icon="el-icon-document">
                Open in new tab
              </el-button>
            </a>
          </template>
        </el-table-column>
        <el-table-column prop="Description" label="Description" />
        <el-table-column prop="LinkType" label="Link" width="100" sortable />
        <el-table-column prop="LinkType" label="Link" width="100" sortable />
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
import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentBrowser',
  components: {},
  data() {
    return {
      documents: []
    }
  },
  async mounted() {
    this.documents = await document.list()
  },
  methods: {
  }
}
</script>
