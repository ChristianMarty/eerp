<template>
  <div class="list-document-container">
    <el-table
      :data="documents"
      style="width: 100%"
    >
      <el-table-column prop="ItemCode" label="Code" width="100">
        <template slot-scope="{ row }">
          <router-link :to="'/document/item/' + row.ItemCode" class="link-type">
            <span> {{ row.ItemCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Category" label="Category" width="100" />
      <el-table-column prop="Name" label="Name" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Document" label="Document" width="200">
        <template slot-scope="{ row }">
          <el-button
            icon="el-icon-document"
            @click="open(row.Path)"
          >Open in new tab</el-button>
        </template>
      </el-table-column>
      <el-table-column prop="CreatedByInitials" label="Initials" width="80" />
      <el-table-column prop="CreationDate" label="Upload Timestamp" width="180" />
    </el-table>
  </div>
</template>

<script>

export default {
  props: { documents: { type: Array, default: Array }},
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
