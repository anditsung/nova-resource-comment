<template>
    <div v-if="authorizedToCreate || hasComments">
        <Heading
            :level="1"
            class="mb-3 flex items-center">
            <span v-html="field.name" />
            <button
                v-if="! loading && field.hasManyRelationship"
                @click="handleCollapsableChange"
                class="rounded border border-transparent h-6 w-6 ml-1 inline-flex items-center justify-center focus:outline-none focus:ring ring-primary-200"
                :aria-label="__('Toggle Collapsed')"
                :aria-expanded="shouldBeCollapsed === false ? 'true' : 'false'"
            >
                <CollapseButton :collapsed="shouldBeCollapsed" />
            </button>
        </Heading>

        <template v-if="! shouldBeCollapsed">
            <Card>
                <div
                    class="border-b border-gray-100 dark:border-gray-700"
                    :class="{'border-transparent' : ! hasComments }"
                    v-if="authorizedToCreate"
                >
                    <div class="w-full py-6 px-6">
                        <textarea
                            class="w-full form-control form-input form-input-bordered py-3 h-auto mt-2"
                            :class="{ 'form-input-border-error' : commentError }"
                            v-model="comment"
                            rows="5"
                            :placeholder="__('Write a comment')"
                        ></textarea>

                        <div class="flex items-start justify-between mt-3">
                            <HelpText class="mt-2 help-text-error" v-if="commentError">
                                {{ __(commentError) }}
                            </HelpText>
                            <DefaultButton
                                class="ml-auto"
                                type="submit"
                                @click="createComment">
                                {{ __('Save Comment') }}
                            </DefaultButton>
                        </div>
                    </div>
                </div>

                <LoadingView
                    :loading="loading"
                >
                    <div v-if="hasComments" >
                        <div class="px-6">
                            <Comment v-for="(comment, index) in comments"
                                     class="py-3 border-b border-gray-100 dark:border-gray-700"
                                     :class="{ 'border-transparent' : index === comments.length - 1}"
                                     :key="index"
                                     :resource="comment"
                                     :resourceName="resourceName"
                                     :canDelete="resource.authorizedToDelete"
                            />
                        </div>
                    </div>
                </LoadingView>
            </Card>
        </template>
    </div>
</template>

<script>
import { mapProps } from 'laravel-nova'
import Collapsable from "nova-mixins/Collapsable"
import InteractsWithResourceInformation from "nova-mixins/InteractsWithResourceInformation"
import mininum from "nova-util/minimum"
import { CancelToken, Cancel } from 'axios'
import Comment from "./Comment"

export default {
    mixins: [
        Collapsable,
        InteractsWithResourceInformation,
    ],

    props: {
        ...mapProps(['resourceId', 'field']),
        resourceName: {},
        resource: {},
    },

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
        if (! this.resourceInformation) {
            return
        }

        await this.getComments()

        this.loading = false
    },

    methods: {
        getComments() {
            if (this.shouldBeCollapsed) {
                this.loading = false
                return
            }

            this.loading = true
            this.commentError = null

            this.$nextTick(() => {
                return mininum(
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

        async handleCollapsableChange() {
            this.loading = true

            this.toggleCollapse()

            if (! this.collapsed) {
                await this.getComments()
            } else {
                this.loading = false
            }
        },
    },

    computed: {
        /**
         * Determine if the index view should be collapsed.
         */
        shouldBeCollapsed() {
            return this.collapsed && this.field.hasManyRelationship != null
        },

        localStorageKey() {
            return `nova.navigation.${this.field.resourceName}.collapsed`
        },

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
