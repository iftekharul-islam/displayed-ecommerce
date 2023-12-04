<template>
  <form v-if="shipping_classes.length > 0">
    <select v-model="shipping_class_id" class="form-control" @change="getCities">
      <option value="">{{ lang.select_shipping_class }}</option>
      <option v-for="(shipping_class,index) in shipping_classes" :key="index" :value="shipping_class.id">
        {{ shipping_class.name }}
      </option>
    </select>
    <select v-if="shipping_class_id" v-model="city_id" class="form-control">
      <option value="">{{ lang.select_city }}</option>
      <option v-for="(city,index) in cities" :key="index" :value="city.id">{{ city.name }}</option>
    </select>
  </form>
</template>
<script>
export default {
  name: "shipping_class",
  props: ['shipping_classes'],
  data() {
    return {
      shipping_class_id: '',
      city_id: '',
      cities: []
    }
  },
  mounted() {
  },
  methods: {
    getCities() {
      let url = this.getUrl('cities-by-shipping-classes/' + this.shipping_class_id);
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.cities = response.data.cities;
        }
      })
    },
  },
}
</script>