<template>

    <p class="my-3">
        <button class="btn btn-primary" @click="add">Add a new user</button>
    </p>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users" @click="edit(user)">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td><button class="btn btn-sm btn-primary">Edit</button></td>
            </tr>
        </tbody>
    </table>

    <modal ref="editor">
        <div class="modal-title">{{ editing.id ? 'Edit user' : 'Add user' }}</div>
        <div class="form-group">
            <label class="form-label">Name</label>
            <input class="form-control" type="text" maxlength="255" v-model="editing.name" />
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control" type="text" maxlength="255" v-model="editing.email" />
        </div>

        <div class="form-group">
            <label class="form-label">Is an administrator</label>

            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="adminFalse" :value="false" v-model="editing.is_admin">
                    <label class="form-check-label" for="adminFalse">No</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="adminTrue" :value="true" v-model="editing.is_admin">
                    <label class="form-check-label" for="adminTrue">Yes</label>
                </div>
            </div>

        </div>

        <div class="form-group" v-if="!editing.is_admin">
            <label class="form-label">Can edit</label>
            <input type="text" class="form-control" placeholder="Any" v-model="restrictToNodes" @change="checkNodes" />
        </div>

        <div class="modal-buttons">
            <button
                class="btn btn-primary"
                :disabled="!valid"
                @click="save"
            >Save</button>
        </div>

        <pre v-text="json" v-if="false"></pre>
    </modal>

</template>
<script>
import Modal from "../Ui/Modal";
export default {
    mounted() {
        this.load();
    },
    data() {
        return {
            users: [],
            editing: null
        }
    },
    computed: {
        restrictToNodes: {
            get() {
                return this.editing.restrict_to_nodes ? this.editing.restrict_to_nodes.join(',') : null;
            },
            set(v) {
                if(!v.trim()) {
                    this.editing.restrict_to_nodes = null;
                    return;
                }
                this.editing.restrict_to_nodes =
                    v.split(',')
                        .map(n => parseInt(n) || n.replace(/[^0-9]+/,""))
            }
        },
        json() {
            return JSON.stringify(this.editing,null,4);
        },
        valid() {
            return this.editing.name && this.editing.email;
        }
    },
    methods: {
        checkNodes() {
            let nodes = this.editing.restrict_to_nodes;
            if(!nodes) {
                return;
            }
            if(nodes) {
                nodes = nodes.filter(n => !!n);
            }
            this.editing.restrict_to_nodes = nodes.length ? nodes : null;
        },
        load() {
            axios.get('/actinite/api/users')
                .then(r => {
                    this.users = r.data.data;
                });
        },
        add() {
            this.editing = {is_admin:false};
            this.$refs.editor.open();
        },
        edit(user) {
            this.editing = JSON.parse(JSON.stringify(user));
            this.$refs.editor.open();
        },
        finishEditing() {
            this.$refs.editor.close();
            this.editing = null;
            this.load();
        },
        save() {
            if(this.editing.id) {
                axios.put('/actinite/api/users/'+this.editing.id,this.editing)
                    .then(this.finishEditing)
                    .catch(e => {
                        console.log(e.response);
                        alert('Unable to save');
                    })
            } else {
                axios.post('/actinite/api/users',this.editing)
                    .then(this.finishEditing)
                    .catch(e => {
                        console.log(e.response);
                        alert('Unable to save');
                    })
            }
        }
    },
    components: {
        Modal
    },
}
</script>
