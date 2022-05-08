<template>
  <div class="order-total">
    <div style="display: flex; justify-content: flex-end; margin-top: 20px">
      <table style="border-collapse:collapse">
        <tr><td style="text-align: right"><b>Total Net [{{total.CurrencyCode}}]:</b></td><td style="width:100px; padding-left: 10px">{{ total.Net }}</td></tr>
        <tr><td style="text-align: right"><b>Total Discount [{{total.CurrencyCode}}]:</b></td><td style="width:100px; padding-left: 10px">{{ total.Discount }}</td></tr>
        <tr><td style="text-align: right"><b>Total VAT [{{total.CurrencyCode}}]:</b></td><td style="width:100px; padding-left: 10px">{{ total.Vat }}</td></tr>
        <tr style="border-top: 1px solid black"><td style="text-align: right"><b>Total [{{total.CurrencyCode}}]:</b></td><td style="width:100px; padding-left: 10px">{{ total.Total }}</td></tr>
      </table>
    </div>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'OrderTotal',
  props: { total: { type: Object, default: {}}},
  data() {
    return {
    }
  },
  mounted() {
  },
  methods: {
  }
}
</script>
