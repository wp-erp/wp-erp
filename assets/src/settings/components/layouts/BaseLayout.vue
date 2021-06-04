<template>
    <div>
        <h2 class="section-title">{{ sectionTitle }}</h2>
        <settings-sub-menu :menus="subMenus" :parent_id="section_id"></settings-sub-menu>

        <div class="settings-box">
            <h3 class="sub-section-title" v-if="subSectionTitle">{{ subSectionTitle }}</h3>
            <p class="sub-section-description" v-if="subSectionDescription">
                {{ subSectionDescription }}
            </p>

            <form action="" class="wperp-form" method="post" @submit.prevent="onFormSubmit">
                <slot></slot>

                <div class="wperp-form-group">
                    <submit-button :text="__( 'Save Changes', 'erp' )" />
                </div>
            </form>
        </div>

    </div>
</template>

<script>
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';

export default {
  name: "BaseLayout",

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  data() {
      return {
          sectionTitle: '',
          subSectionTitle: '',
          subSectionDescription: '',
          subMenus: []
      }
  },

  props: {
        section_id: {
            type: String,
            required: true
        },
        sub_section_id: {
            type: String,
            required: true
        },
        onFormSubmit: {
            type: Function,
            required: true
        },
  },

  created() {
      // process the menus and get the sections data
      const menus      = erp_settings_var.erp_settings_menus;
      const parentMenu = menus.find( menu => menu.id === this.section_id );

      this.sectionTitle = parentMenu.label + ' Management';
      this.subMenus     = parentMenu.sections;

      const fields = parentMenu.fields[this.sub_section_id];

      if ( fields.length > 0 ) {
        this.subSectionTitle       = fields[0].title;
        this.subSectionDescription = fields[0].desc;
      }

  }
}

</script>
