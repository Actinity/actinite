<template>

<ul class="pagination"
    :class="{'pagination-mini':!!mini}"
    v-if="pagesToShow.length > 1 || mini"
>
    <template v-if="showButtons || mini">
        <li class="page-item" :class="{'disabled': page === 1}">
            <a
                class="page-link"
                href="#"
                @click.prevent="decr"
            ><i class="fas fa-angle-left"></i></a>
        </li>
    </template>

	<li
		v-if="mini"
		class="page-item page-number"
	>
		<span class="page-link" title="Page">{{ page }} / {{ pagination_data.last_page}}</span>
	</li>

	<li
		v-if="!mini"
		class="page-item page-number"
		v-for="p in pagesToShow"
		:class="{'active':p === page,'disabled':p === '...'}"
	>
		<a class="page-link" href="#" @click.prevent="selectPage(p)">
			{{ p }}
		</a>
	</li>

    <template v-if="showButtons || mini">
        <li class="page-item" :class="{'disabled': page === pagination_data.last_page}">
            <a
                class="page-link"
                href="#"
                @click.prevent="incr"
            ><i class="fas fa-angle-right"></i></a>
        </li>
    </template>

</ul>

<!-- Render a blank, hidden cell to avoid having a weird '1' hanging around,
    but to still keep consistent height -->

<ul class="pagination justify-content-end pagination-spacer" v-else>
    <li class="page-item active"><a href="#" class="page-link" @click.prevent>1</a></li>
</ul>

</template>
<script>
export default {
    props: [
        'mini'
    ],
    inject: ['pagination_data','pagination_wrapper'],
    data() {
        return {
            page: 1,
        }
    },
    watch: {
        pagination_data: {
            handler(v) {
                this.page = v.current_page
            },
            deep: true
        }
    },
    computed: {
        showButtons() {
            return this.pagination_data.last_page > 1;
        },
        availablePages() {
            let pages = [];
            for(let i = 0; i < this.pagination_data.last_page; i++) {
                pages.push(i+1);
            }
            return pages;
        },
        pagesToShow() {
            if(this.pagination_data.last_page < 8) {
                return this.availablePages;
            }
            let pages = this.availablePages,top = [1,'...'], tail = ['...',this.pagination_data.last_page];

            if(this.page < 5) {
                pages = pages.slice(0,5).concat(tail);
            } else if(this.page > this.pagination_data.last_page - 4) {
                pages = top.concat(pages.slice(-5));
            } else {
                pages = top.concat(pages.slice(this.page - 2,this.page + 1)).concat(tail);
            }

            return pages;


        }
    },
    methods: {
        selectPage(page) {
            this.pagination_wrapper.updatePage(page);
        },
        decr() {
            if(this.page > 1) {
                this.selectPage(this.page - 1);
            }
        },
        incr() {
            if(this.page < this.pagination_data.last_page) {
                this.selectPage(this.page + 1);
            }
        }
    }
}
</script>
<style scoped lang="scss">
    .pagination {
        margin: 0;
        user-select: none;
		cursor: pointer;
        display: inline-flex;
    }
    .page-link {
        width: 40px;
        text-align: center;
    }
    .pagination-spacer {
        pointer-events: none;
        visibility: hidden;
    }
    .pagination-mini .page-number .page-link {
        width: 80px;
		white-space: nowrap;

		&:hover {
			background-color: inherit;
		}
    }
</style>
