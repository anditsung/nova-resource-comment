<template>
    <div v-if="authorizedToCreate || hasComments">
        <heading :level="1" class="mb-3" v-html="field.name" />

        <card>
            <div
                class="border-b border-40"
                :class="{'remove-bottom-border' : !hasComments }"
                v-if="authorizedToCreate"
            >
                <div class="py-6 px-6">
                    <textarea
                        class="w-full form-control form-input form-input-bordered py-3 h-auto mt-2"
                        :class="{ 'border-danger' : commentError }"
                        v-model="comment"
                        rows="5"
                        :placeholder="__('Write a comment')"
                    ></textarea>

                    <help-text class="help-text mt-2 text-danger" v-if="commentError">
                        {{ __(commentError) }}
                    </help-text>

                    <div class="flex justify-end">
                        <button class="btn btn-default btn-primary inline-flex items-center relative mt-4"
                                type="submit"
                                @click="createComment">
                            {{ __('Save Comment') }}
                        </button>
                    </div>
                </div>
            </div>

            <loading-view
                :loading="loading"
            >
                <div v-if="hasComments" >
                    <div class="px-6">
                        <comment v-for="(comment, index) in comments"
                                 class="py-3 border-b border-40"
                                 :class="{ 'remove-bottom-border' : index === comments.length - 1}"
                                 :key="index"
                                 :resource="comment"
                                 :resourceName="resourceName"
                                 :canDelete="resource.authorizedToDelete"
                        />
                    </div>
                </div>
            </loading-view>
        </card>
    </div>
</template>

<script>
import {
    Minimum,
    InteractsWithResourceInformation,
} from 'laravel-nova'
import { CancelToken, Cancel } from 'axios'
import Comment from "./Comment"

export default {
    mixins: [
        InteractsWithResourceInformation,
    ],

    props: ['resourceName', 'resourceId', 'resource', 'field'],

    components: {
        Comment,
    },

    data: () => ({
        canceller: null,
        loading: true,
        comment: '',
        commentError: '',
        commentResponse: null,
        comments: []
    }),

    async created() {
        if (Nova.missingResource(this.field.resourceName)) {
            return this.$router.push({ name: '404' })
        }

        await this.getComments()

        this.loading = false
    },

    methods: {
        getComments() {
            this.loading = true

            this.$nextTick(() => {
                return Minimum(
                    Nova.request().get('/nova-api/' + this.field.resourceName, {
                            params: this.commentRequestQueryString,
                            cancelToken: new CancelToken(canceller => {
                                this.canceller = canceller
                            }),
                        }),
                        300
                    )
                    .then(({ data }) => {
                        this.comments = []

                        this.commentResponse = data
                        this.comments = data.resources

                        this.loading = false

                        Nova.$emit('comments-loaded')
                    })
                    .catch(e => {
                        if (e instanceof Cancel) {
                            return
                        }

                        this.loading = false

                        Nova.error(this.__(e))
                    })
            })
        },

        createComment() {
            if (! this.comment) {
                this.commentError = "Please write a comment"
                return
            }

            Nova.request().post('/nova-api/' + this.field.resourceName, {
                    comment: this.comment,
                    commentable_type: this.resourceName,
                    commentable: this.resourceId
                })
                .then(() => {
                    this.resetComment()

                    this.getComments()

                    Nova.success(this.__('A new comment has been created.'))
                })
                .catch(response => {
                    Nova.error(this.__(response))
                })
        },

        resetComment() {
            this.comment = ''
            this.commentError = ''
        },
    },

    computed: {
        commentRequestQueryString() {
            return {
                orderBy: 'created_at',
                orderByDirection: 'desc',
                viaResource: this.resourceName,
                viaResourceId: this.resourceId,
                viaRelationship: this.field.hasManyRelationship,
                relationshipType: 'hasMany',
            }
        },

        hasComments() {
            return this.comments.length > 0
        },
    }
}
</script>
