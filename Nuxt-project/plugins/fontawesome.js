import { library, config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { fab } from '@fortawesome/free-brands-svg-icons';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
// import Vue from 'vue';
// import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
config.autoAddCss = true;

  library.add(fas, far, fab);

  export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.component("font-awesome-icon", FontAwesomeIcon, {});
  });

  // Vue.use(BootstrapVue, {});
  // Vue.use(BootstrapVueIcons, {})