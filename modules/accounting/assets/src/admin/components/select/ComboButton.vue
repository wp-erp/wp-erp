<template>
    <div class="wperp-select-container select-primary">
        <div class="wperp-selected-option">
      		<div class="left-part" v-on="optionSelected(options[0])">
				{{ options[0].text }}
			</div>

      		<div class="right-part" @click="toggleButtons">
				<span class="caret"></span>
			</div>
        </div>

        <ul class="wperp-options" v-if="showMenu">
            <li :key="index" v-for="(option, index) in options">

                <a href="#" @click.prevent="optionSelected(option)">
                    {{ option.text }}
                </a>
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
                this.ssm = false;
                this.$root.$emit('combo-btn-select', option);
            },

            toggleButtons() {
				this.showMenu = !this.showMenu;
            }
        }
    }
</script>

<style lang="less">
	@theme-color: #1A9ED4;
	@theme-border-color: #ECECEC;

	.wperp-select-container {
		display: inline-flex;
		width: auto;
		position: relative;
		.wperp-selected-option {
			display: flex;
			justify-content: space-between;
			background: @theme-color;
			color: #fff;
			border-radius: 3px;
			white-space: nowrap;
			cursor: pointer;
			min-width: 150px;
		}
		a:hover {
			text-decoration: none;
		}
	}

	.wperp-options {
		position: absolute;
		top: 100%;
		left: 0;
		z-index: 1000;
		min-width: 100%;
		white-space: nowrap;
		list-style: none;
		text-align: left;
		background-color: #fff;
		border: 1px solid @theme-border-color;
		border-radius: 3px;
		box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
		background-clip: padding-box;
		list-style: none;
		margin: 6px 0 0;	
		padding: 5px 0;
		font-size: 14px;
		display: block;
		&:after,
		&:before {
			content: '';
		    position: absolute;
		    top: -6px;
		    right: 20px;
		    border-bottom: 5px solid @theme-border-color;
		    border-right: 5px solid transparent;
		    border-left: 5px solid transparent;
		}
		&:after {
		    top: -4px;
		    right: 20px;
		    border-bottom-color: #fff;
		}
		li {
			overflow: hidden;
			width: 100%;
			position: relative;
			margin: 0;
			a {
				padding: 5px 20px;
				display: block;
				clear: both;
				font-weight: normal;
				line-height: 1.6;
				color: #333333;
				white-space: nowrap;
				text-decoration: none;
				&:hover {
					background: @theme-border-color;
					color: @theme-color;
				}
			}
		}
	}
	.caret {
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

</style>
