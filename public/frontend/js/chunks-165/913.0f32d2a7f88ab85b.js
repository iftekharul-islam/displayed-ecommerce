"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[913],{913:(t,s,a)=>{a.r(s),a.d(s,{default:()=>l});const e={name:"blogs",data:function(){return{page:1,activeClass:"",form:{sort:"newest",slug:this.$route.params.slug,title:null},loading:!1,is_shimmer:!1,next_page_url:!1}},components:{shimmer:a(5662).Z},mounted:function(){0==this.lengthCounter(this.blogs)&&this.allBlogs(),this.lengthCounter(this.blogs)>0&&(this.is_shimmer=!0)},computed:{blogs:function(){return this.$store.getters.getBlogs},shimmer:function(){return this.$store.state.module.shimmer}},watch:{$route:function(t){"blogs"==t.name&&(this.form.slug=null),this.$store.dispatch("blogs",this.form)}},methods:{loadMoreData:function(){var t=this;this.loading=!0,this.$Progress.start(),axios.get(this.next_page_url,{params:this.form}).then((function(s){if(s.data.error)toastr.error(s.data.error,t.lang.Error+" !!");else{t.loading=!1;var a=s.data.blogs.data;if(a.length>0)for(var e=0;e<a.length;e++)t.blogs.data.push(a[e]);t.$Progress.finish()}t.next_page_url=s.data.blogs.next_page_url}))},filterBlogs:function(){this.page=1,this.allBlogs(this.form)},allBlogs:function(){var t=this;this.loading=!0;var s=this.getUrl("home/blogs?page=1");axios.get(s,{params:this.form}).then((function(s){t.is_shimmer=!0,t.loading=!1,s.data.error?(t.$Progress.fail(),toastr.error(s.data.error,t.lang.Error+" !!")):(t.$store.commit("getBlogs",s.data.blogs),t.next_page_url=s.data.blogs.next_page_url,t.page++,t.$Progress.finish())})).catch((function(s){t.loading=!1,t.is_shimmer=!0,t.$Progress.fail(),s.response&&422==s.response.status&&toastr.error(response.data.error,t.lang.Error+" !!")}))}}},i=e;const l=(0,a(1900).Z)(i,(function(){var t=this,s=t._self._c;return s("section",{staticClass:"sg-blog-section sg-filter",class:t.activeClass},[s("div",{staticClass:"container"},[s("div",{staticClass:"title blog-header justify-content-between"},[s("h1",[t._v("Blog News")]),t._v(" "),s("div",{staticClass:"right-content"},[s("select",{directives:[{name:"model",rawName:"v-model",value:t.form.sort,expression:"form.sort"}],staticClass:"form-control",on:{change:[function(s){var a=Array.prototype.filter.call(s.target.options,(function(t){return t.selected})).map((function(t){return"_value"in t?t._value:t.value}));t.$set(t.form,"sort",s.target.multiple?a:a[0])},t.filterBlogs]}},[s("option",{attrs:{value:"newest"}},[t._v(t._s(t.lang.newest))]),t._v(" "),s("option",{attrs:{value:"oldest"}},[t._v(t._s(t.lang.oldest))]),t._v(" "),s("option",{attrs:{value:"viewed"}},[t._v(t._s(t.lang.most_viewed))])]),t._v(" "),s("div",{staticClass:"d-flex gap-3"},[s("div",{staticClass:"sg-search"},[s("div",{staticClass:"search-form blog-search"},[s("form",{on:{submit:function(s){return s.preventDefault(),t.filterBlogs.apply(null,arguments)}}},[s("input",{directives:[{name:"model",rawName:"v-model",value:t.form.title,expression:"form.title"}],staticClass:"form-control",attrs:{type:"text",placeholder:t.lang.search_blog},domProps:{value:t.form.title},on:{input:function(s){s.target.composing||t.$set(t.form,"title",s.target.value)}}}),t._v(" "),t.loading?s("loading_button"):s("button",{attrs:{type:"submit"}},[s("span",{staticClass:"mdi mdi-name mdi-magnify"})])],1)])]),t._v(" "),s("ul",{staticClass:"filter-tabs global-list"},[s("li",{staticClass:"grid-view-tab",class:{active:"grid-view-tab"==t.activeClass||""==t.activeClass},on:{click:function(s){t.activeClass="grid-view-tab"}}},[s("span",{staticClass:"mdi mdi-name mdi-grid"})]),t._v(" "),s("li",{staticClass:"list-view-tab",class:{active:"list-view-tab"==t.activeClass},on:{click:function(s){t.activeClass="list-view-tab"}}},[s("span",{staticClass:"mdi mdi-name mdi-format-list-bulleted"})])])])])]),t._v(" "),t.is_shimmer?s("div",{staticClass:"row"},t._l(t.blogs.data,(function(a,e){return s("div",{key:e,staticClass:"col-md-6 col-lg-3"},[s("div",{staticClass:"post"},[s("div",{staticClass:"entry-header"},[s("div",{staticClass:"entry-thumbnail"},[s("router-link",{attrs:{to:{name:"blog.details",params:{slug:a.slug}}}},[s("img",{staticClass:"img-fluid",attrs:{loading:"lazy",src:a.thumbnail,alt:a.title}})])],1)]),t._v(" "),s("div",{staticClass:"entry-content"},[s("router-link",{attrs:{to:{name:"blog.details",params:{slug:a.slug}}}},[s("h1",{staticClass:"entry-title text-ellipse"},[t._v(t._s(a.title))])]),t._v(" "),s("p",{staticClass:"text-ellipse"},[t._v(t._s(a.short_description))]),t._v(" "),s("router-link",{attrs:{to:{name:"blog.details",params:{slug:a.slug}}}},[t._v("\n              "+t._s(t.lang.read_more)+"\n            ")])],1)])])})),0):t.shimmer?s("div",{staticClass:"row"},t._l(12,(function(t,a){return s("div",{key:a,staticClass:"col-md-6 col-lg-3"},[s("div",{staticClass:"post"},[s("shimmer")],1)])})),0):t._e(),t._v(" "),t.next_page_url&&!t.loading?s("div",{staticClass:"show-more mt-4"},[s("a",{staticClass:"btn btn-primary",attrs:{href:"javaScript:void(0)"},on:{click:function(s){return t.loadMoreData()}}},[t._v(t._s(t.lang.show_more))])]):t._e(),t._v(" "),s("div",{directives:[{name:"show",rawName:"v-show",value:t.loading,expression:"loading"}],staticClass:"col-md-12 text-center show-more"},[s("loading_button",{attrs:{class_name:"btn btn-primary"}})],1)])])}),[],!1,null,null,null).exports},5662:(t,s,a)=>{a.d(s,{Z:()=>i});const e={name:"shimmer.vue",props:["height"],data:function(){return{style:{height:this.height+"px"}}}};const i=(0,a(1900).Z)(e,(function(){var t=this;return(0,t._self._c)("img",{staticClass:"shimmer",style:[t.height?t.style:null],attrs:{src:t.getUrl("public/images/default/preview.jpg"),alt:"shimmer"}})}),[],!1,null,null,null).exports}}]);