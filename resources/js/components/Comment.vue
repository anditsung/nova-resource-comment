<template>
    <div>
        <div class="flex items-center w-full">
            <div class="flex items-center">
                <router-link
                    v-if="commenter.viewable && commenter.value"
                    :to="{
                        name: 'detail',
                        params: {
                            resourceName: resourceName,
                            resourceId: commenter.belongsToId
                        }
                    }"
                    class="no-underline font-bold dim text-primary"
                    v-text="commenter.value"
                />
                <div v-else-if="commenter.value" class="font-bold">{{ commenter.value }}</div>
                <div v-else>-</div>
                <div class="ml-2 italic text-sm">comment on</div>
                <div class="ml-2 italic">{{ commentAtFormatted }}</div>
            </div>
<!--            <div class="ml-auto" v-if="canDelete">-->
<!--                <button type="button" @click="deleteComment">-->
<!--                    Delete-->
<!--                </button>-->
<!--            </div>-->
        </div>
        <div class="w-full mt-2 whitespace-pre-wrap" v-html="comment.value" />
    </div>
</template>

<script>
export default {
    props: {
        resource: {
            type: Object,
            required: true
        },
        resourceName: {
            type: String,
            required: true,
        },
        canDelete: {
            type: Boolean,
            default: false,
        }
    },

    methods: {
        deleteComment() {

        },
    },

    computed: {
        commenter() {
            return _.find(this.resource.fields, { attribute: 'commenter' })
        },

        comment() {
            return _.find(this.resource.fields, { attribute: 'comment' })
        },

        commentAt() {
            return _.find(this.resource.fields, { attribute: 'created_at' })
        },

        commentAtFormatted() {
            return new Date(this.commentAt.value).toLocaleString()
        }
    }
}
</script>
