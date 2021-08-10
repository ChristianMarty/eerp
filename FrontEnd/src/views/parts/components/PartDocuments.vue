<template>
  <div class="document-container">
    <aside>
      <b>{{ data.Type }} - {{ data.Description }} </b>
      <spacer />
      <div class="float-right">
        <a :href="data.Path" target="blank">
          <el-button
            icon="el-icon-document"
          >
            Open in new tab
          </el-button>
        </a>
        <el-button
          icon="el-icon-document"
          style="margin-left: 50px"
          @click="openPane"
        >
          Open in right pane
        </el-button>
      </div>
    </aside>
  </div>
</template>

<script>
export default {
  props: {
    data: { type: Object, default: null },
    documentPath: { type: String, default: null }
  },
  data() {
    return {
    }
  },
  mounted() {

  },
  methods: {
    openPane() {
      this.$emit('changeDocument', this.data.Path)
    }
  }
}
</script>
