<template>
  <div class="track-dialog">
    <el-dialog
      title="Track Item"
      :visible.sync="visible"
      width="50%"
      center
      :before-close="closeDialog"
      @open="loadData()"
    >
      <el-table
        ref="itemTable"
        :data="trackData"
        border
        style="width: 100%"
        :cell-style="{ padding: '0', height: '30px' }"
      >
        <el-table-column prop="Type" label="Type" width="120" sortable />

        <el-table-column label="Reference" sortable>
          <template slot-scope="{ row }">
            <template v-if="row.Type == 'Part Stock'">
              <router-link :to="'/stock/item/' + row.Barcode" class="link-type">
                <span>{{ row.Barcode }} </span>
              </router-link>
              <span style="float: right;"> Original Quantity: {{ row.CreateQuantity }} </span>
            </template>
            <template v-if="row.Type == 'Inventory'">
              <router-link
                :to="'/inventory/item/' + row.Barcode"
                class="link-type"
              >
                <span>{{ row.Barcode }} </span>
              </router-link>
              <span style="float: right;"> {{ row.Description }} </span>
            </template>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'TrackDialog',
  props: { receivalId: { type: Number, default: 0 }, visible: { type: Boolean, default: false }},
  data() {
    return {
      trackData: null
    }
  },
  mounted() {
  },
  methods: {
    loadData() {
      this.getTrackData()
    },
    getTrackData() {
      requestBN({
        url: '/purchasing/item/track',
        methood: 'get',
        params: {
          ReceivalId: this.$props.receivalId
        }
      }).then(response => {
        this.trackData = response.data
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>
