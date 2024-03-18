<template>
  <div class="app-container">
    <el-table :data="renderers" border style="width: 100%">
      <el-table-column prop="Name" sortable label="Name" width="220">
        <template slot-scope="{ row }">
          <router-link :to="'/renderer/' + row.Id" class="link-type">
            <span>{{ row.Name }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="DatasetName" label="Dataset" sortable width="240" />
      <el-table-column prop="MediumName" label="Medium" sortable width="300" />
      <el-table-column prop="Language" label="Language" sortable width="120" />
    </el-table>
  </div>
</template>

<script>
import Renderer from '@/api/renderer'
const renderer = new Renderer()

export default {
  name: 'RendererList',
  components: {},
  data() {
    return {
      renderers: []
    }
  },
  async mounted() {
    this.renderers = await renderer.list()
  },
  methods: {
  }
}
</script>
