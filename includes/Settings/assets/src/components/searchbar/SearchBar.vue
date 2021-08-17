
<template>
	<div>
		<div class="search-area">
			<input type="search" placeholder="Search" class="input-searchbar" v-model="searchText" />

			<svg width="12px" height="12px" viewBox="0 0 12 12" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					<g id="search" transform="translate(-1163.000000, -73.000000)" fill="#CBCBCB" fill-rule="nonzero">
						<g id="search-inner" transform="translate(1012.000000, 64.000000)">
							<path d="M162.674231,19.2599522 L160.074438,16.6602072 C160.060622,16.6463904 160.044414,16.6367331 160.029976,16.6238725 C160.54153,15.8478884 160.840096,14.919012 160.840096,13.9200956 C160.840096,11.2027888 158.637307,9 155.920048,9 C153.202789,9 151,11.2027888 151,13.9200478 C151,16.637259 153.202741,18.8400956 155.92,18.8400956 C156.918964,18.8400956 157.847793,18.5415299 158.623777,18.0299761 C158.636637,18.0443665 158.646247,18.0605737 158.660064,18.0743904 L161.259904,20.6742311 C161.650454,21.0647331 162.283633,21.0647331 162.674231,20.6742311 C163.064733,20.2836813 163.064733,19.650502 162.674231,19.2599522 Z M155.920048,17.1344701 C154.144717,17.1344701 152.705578,15.6953307 152.705578,13.9200478 C152.705578,12.1447171 154.144765,10.7055777 155.920048,10.7055777 C157.695283,10.7055777 159.13447,12.1447649 159.13447,13.9200478 C159.13447,15.6953307 157.695283,17.1344701 155.920048,17.1344701 Z" id="Shape"></path>
						</g>
					</g>
				</g>
			</svg>

			<div class="search-suggestion-area" v-if="searchText.length && searchedItems.length">
				<div class="single-suggestion-item" v-for="(item, index) in searchedItems" :key="index">
					<router-link :to="item.url">
						<h4 v-html="item.parentLabel"></h4>
						<h6 v-html="`# ${item.label}`"></h6>
						<p v-if="item.desc" v-html="item.desc"></p>
					</router-link>
				</div>
			</div>

            <div class="search-suggestion-area" v-if="searchText.length && ! searchedItems.length">
                <div class="single-suggestion-item">
                    <p class="text-danger">
                        {{ __('Sorry ! Nothings found for your query. Please try again !', 'erp') }}
                    </p>
                </div>
            </div>
		</div>
	</div>
</template>

<script>
export default {
	name: "SearchBar",
	data() {
		return {
			searchText   : "",
			allItems     : [],
			searchedItems: []
		};
	},

	created() {
		this.getAllItems();
	},

	methods: {
		/**
		 * Get all items in searchable list
		 */
		getAllItems() {
			const menus = erp_settings_var.erp_settings_menus;
			let allItems = [];

			menus.forEach((menu, index) => {
				const searchItem = {
					id         : menu.id,
					label      : menu.label,
					parentLabel: this.getSectionTitle(menu),
					desc       : menu.desc,
					parentId   : null,
					icon       : menu.icon,
					url        : `/${menu.id}`,
				};
				allItems.push(searchItem);

				// Push fields in the array
				const isFieldArray = this.isArray(menu.fields);

				Object.keys(menu.fields).forEach((key) => {
					const fieldItem = menu.fields[key];

                    if( isFieldArray ) {
                        if ( typeof fieldItem.title != "undefined" && fieldItem.title.length > 0 ) {
                            const newFeildItem = {
                                ...searchItem,
                                id         : fieldItem.id,
                                label      : fieldItem.title,
                                parentLabel: searchItem.parentLabel,
                                desc       : fieldItem.desc,
                            };
                            allItems.push(newFeildItem);
                        }
                    } else {
                        const subSectionKey = key;

                        Object.keys(fieldItem).forEach((subKey) => {
					        const subField = fieldItem[subKey];

                            if ( typeof subField.title != "undefined" && subField.title.length > 0 ) {
                                const newFeildItem = {
                                    ...searchItem,
                                    id         : subField.id,
                                    label      : subField.title,
                                    parentLabel: searchItem.parentLabel,
                                    desc       : subField.desc,
                                    url        : `${searchItem.url}/${subSectionKey}`
                                };

                                allItems.push(newFeildItem);
                            }
                        });
                    }
				});
			});

			this.allItems      = allItems;
			this.searchedItems = allItems;
		},
	},

	watch: {
		/**
		 * Watch `search` text key using Regex and filter by -
		 * Label or Parent Menu Label or description
		 *
		 * @return void
		 */
		searchText: function () {
            const searchText = this.searchText !== null ? String( this.searchText ).toLowerCase() : ''
			const regex      = new RegExp(searchText, "i");

            this.searchedItems = this.allItems.filter( item => {
                const label             = ( typeof item.label !== 'undefined' && item.label !== null ) ? String( item.label ).toLowerCase() : '';
                const parentLabel       = ( typeof item.parentLabel !== 'undefined' && item.parentLabel !== null ) ? String( item.parentLabel ).toLowerCase() : '';
                const desc              = ( typeof item.desc !== 'undefined' && item.desc !== null ) ? String( item.desc ).toLowerCase() : '';

                const fullMatchedString = `${label} ${parentLabel} ${desc}`;
				return regex.test(fullMatchedString);
			});
		},

        /**
         * If any route change, then reset the search bar
         */
        $route: function() {
            this.searchText    = '';
            this.searchedItems = this.allItems;
        }
	},
};
</script>
