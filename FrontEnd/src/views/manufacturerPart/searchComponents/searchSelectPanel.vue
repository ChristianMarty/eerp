<template>
  <div class="el-transfer-panel">

    <p class="el-transfer-panel__header">
      <el-checkbox
        v-model="handleAllChecked"
        
        @change="handleAllCheckedChange"
      >
        {{ options.Name }}
        <span>{{ options.Options.length }}</span>
      </el-checkbox>

    </p>

    <div class="el-transfer-panel__body">
      <!-- <el-input
        v-if="filterable"
        v-model="query"
        class="el-transfer-panel__filter"
        size="small"
        :placeholder="placeholder"
        @mouseenter.native="inputHover = true"
        @mouseleave.native="inputHover = false"
      >
        <i
          slot="prefix"
          :class="['el-input__icon', 'el-icon-' + inputIcon]"
          @click="clearQuery"
        />
      </el-input>-->

      <el-checkbox-group
        v-model="checked"

        class="el-transfer-panel__list"
      >
        <el-checkbox
          v-for="option in options.Options"
          :key="option.id"
          class="el-transfer-panel__item"
          :label="option.Name"
        >
          {{ option.Name }}
          <option-content :option="option.Name" />
        </el-checkbox>
      </el-checkbox-group>

      <!-- <p
        class="el-transfer-panel__empty"
      >{{ t('el.transfer.noMatch') }}</p>
      <p
        v-show="data.length === 0 && !hasNoMatch"
        class="el-transfer-panel__empty"
      >{{ t('el.transfer.noData') }}</p>  -->

    </div>
    <p class="el-transfer-panel__footer"> fdgdgfd</p>

  </div>
</template>

<script>

export default {
  name: 'BaseClassSearchContainer',
  props: { options: { type: Object, default: null }},
  data() {
    return {
      handleAllChecked: false,
      items: ['sdf', 'asd', 'ert', 'poi', 'sdf', 'asd', 'ert', 'poi'],

      title: 'test',
      checked: [],
      checkedSummary: '0/16',
      isIndeterminate: false
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {

    handleAllCheckedChange(value) {
      if (value) {
        this.checked = this.options.Options.map(item => item.Id)
      } else {
        this.checked = []
      }
      this.handleAllChecked = !value
    }

  }
}
</script>
