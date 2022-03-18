<template>
    <div>
        <form @submit.prevent="submit">
            <div class="custom-file">
                <input type="file"
                       class="custom-file-input"
                       id="customFile"
                       @change="onAttachmentChange"
                >
            </div>
            <input type="checkbox" v-model="saveDB">Save to database

            <div class="form-group">
                <button type="submit">Submit</button>
            </div>
        </form>

        <div>
            Avg price: {{ result.avgPrice }}
        </div>
        <div>
            Total houses sold: {{ result.totalSold }}
        </div>
        <div>
            No of crimes in 2011: {{ result.crimes }}
        </div>
        <div>
            Avg price per year in London area:
        </div>
        <div>
                <div v-for="(item, index) in result.avgPriceYear" >
                    {{ index }}: {{ item }}
                </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            attachment: null,
            saveDB: false,
            result: {},
        }
    },
    methods: {
        submit() {
            const config = {'content-type': 'multipart/form-data'}
            const formData = new FormData()
            formData.append('attachment', this.attachment)
            formData.append('saveDB', this.saveDB)
            console.log(formData);
            axios.post('/', formData, config)
                .then(response => this.result = response.data)
                .catch(error => console.log(error))
            console.log(this.result.avgPriceYear)
        },

        onAttachmentChange(e) {
            this.attachment = e.target.files[0];
            // const file = e.target.files[0];
            // const reader = new FileReader();
            // reader.onload = e => console.log(e.target.result);
            // reader.readAsBinaryString(file);
        },
    },
    watch: {}
}
</script>
