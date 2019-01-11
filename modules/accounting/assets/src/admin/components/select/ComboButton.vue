<template>
    <div class="wperp-select-container select-primary combo-btns">
        <div class="wperp-selected-option">
      		<div class="left-part" @click="optionSelected(options[0])">
				<button class="btn-fake">{{ options[0].text }}</button>
			</div>

      		<div class="right-part" @click="toggleButtons">
				<span class="btn-caret"></span>
			</div>
        </div>

        <ul class="wperp-options" v-if="showMenu">
            <li :key="index" v-for="(option, index) in options.slice(1)">
                <button class="btn-fake" @click="optionSelected(option)">{{ option.text }}</button>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: 'ComboButton',

        data() {
            return {
				showMenu: false
            }
        },
        props: {
            options: {
				type: Array,
				default: []
            }
        },

        methods: {
            optionSelected(option) {
                this.showMenu = false;
                this.$root.$emit('combo-btn-select', option);
            },

            toggleButtons() {
				this.showMenu = !this.showMenu;
            }
        }
    }
</script>

<style lang="less">
	.combo-btns {
		.wperp-selected-option {
			padding: 0 !important;
		}

		 .btn-fake {
			 border: 0;
			 box-shadow: none;
			 background: none;
			 cursor: pointer;
		 }

		.btn-caret {
			border-top: 4px solid #fff;
			border-right: 4px solid transparent;
			border-left: 4px solid transparent;
		}

		.left-part,
		.right-part {
			float: left;

		}

		.left-part {
			width: 80%;
			border-top-left-radius: 3px;
			border-bottom-left-radius: 3px;
			line-height: 2;
			text-align: center;

			.btn-fake {
				color: #fff;
			}
		}

		.right-part {
			width: 20%;
			background: #03A9F4;
			display: flex;
			align-items: center;
			justify-content: center;
			border-top-right-radius: 3px;
			border-bottom-right-radius: 3px;
		}

		.wperp-options {
			.btn-fake {
				padding: 5px 20px;
				display: block;
				clear: both;
				font-weight: 400;
				line-height: 1.6;
				color: #333;
				white-space: nowrap;
				text-decoration: none;
				width: 100%;

				&:hover {
					background: #ececec;
    				color: #1a9ed4;
				}
			}
		}
	}

</style>
