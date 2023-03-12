<template>
  <div class="list-document-container">
    <el-table
      :data="documents"
      style="width: 100%"
      @row-click="(row)=>open(row.Path)"
    >
      <el-table-column prop="Barcode" label="Doc No" width="100" />
      <el-table-column prop="Description" label="Description">
        <template slot-scope="{ row }">
          {{ row.Description }}
        </template>
      </el-table-column>
      <el-table-column prop="Note" label="Note" />
      <el-table-column prop="Type" label="Type" width="100" />
      <el-table-column prop="Document" label="Document">
        <template slot-scope="{ row }">
          <el-button
            icon="el-icon-document"
            @click="open(row.Path)"
          >Open in new tab</el-button>
        </template>
      </el-table-column>
      <el-table-column prop="LinkType" label="Link" width="100" />
      <el-table-column prop="CreationDate" label="Upload Timestamp" width="180" />
    </el-table>
  </div>
</template>

<script>

export default {
  props: { documents: { type: Object, default: null }},
  data() {
    return {
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    open(path) {
      window.open(path, '_blank').focus()
    }
  }
}

</script>
