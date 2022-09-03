<script defer>

//GLOBAL VARIABLES
const hostname = window.location.href

//MODELS
const User = function ( { id, name, email, city, phone, company, birthdate, img } ) {
    this.id = id
    this.name = name
    this.email = email
    this.phone = phone
    this.birthdate = birthdate ? birthdate : null
    this.img = img ? img : null
 
    this.company = {
        name: company.name
    }

    this.database = false
    this.loading = false

    this.update = ({img, birthdate}) => new Promise((resolve, reject) => {

        const that = this

        $.ajax({
            url: hostname + 'users/' + that.id,
            data: {
                img: img,
                birthdate: birthdate
            },
            type:'put',
            success: function (response) {
                that.img = img
                that.birthdate = birthdate
                resolve(response)
            },
            error:function(x,xs,xt){
                reject('No se pudo guardar el contenido')
            }
        })


    })

    this.save = () => new Promise((resolve, reject) => {

        const that = this

        $.ajax({
            url: hostname + 'users',
            data: {
                name: this.name,
                email: this.email,
                phone: this.phone,
                company: this.company
            },
            type:'post',
            success: function (response) {
                that.id = response
                that.loading = false
                that.database = true
                resolve(response)
            },
            error:function(x,xs,xt){
                that.loading = false
                that.database = false
                reject('No se pudo guardar el contenido')
            }
        })


    })
}

//FUNCTIONS
const getExternalUsers = () => new Promise((resolve, reject) => {

    $.ajax({
        url: 'https://jsonplaceholder.typicode.com/users',
        //data:{'name':"luis"},
        type:'get',
        success: function (response) {

            const users = []

            response.forEach(item => {
                item.id = null
                users.push( new User(item) )
            })

          
            resolve(users)

        },
        error:function(x,xs,xt){
            console.error('typicode service is not available.')
            resolve([])
        }
    })

})


const getUsers = () => new Promise((resolve, reject) => {

    $.ajax({
        url: hostname + 'users',
        //data:{'name':"luis"},
        type:'get',
        success: function (response) {

            const users = []

            response.forEach(item => {
                users.push( new User(item) )
            })

            resolve(users)

        },
        error:function(x,xs,xt){
            console.error('server service is not available.')
            resolve([])
        }
    })

})



$( document ).ready( async function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')     
        }
    })

    const externalUsers = await getExternalUsers()
    const users = await getUsers()

    const dataTableData = []
    
    users.forEach( item => {

        const inDatabase = externalUsers.some( element =>  item.email == element.email )

        if(inDatabase){
            item.database = true
        }

        dataTableData.push( item )


    })

    externalUsers.forEach( item => {

        const exist = dataTableData.some( element => element.email == item.email)

        if(!exist){
            dataTableData.push( item )
        }

    })

    $('#table_users').DataTable({
        "data": dataTableData ,

        "columns": [ 
            {
                "title": "Nombre",
                "data": "name",
            },
            {
                "title": "Email",
                "data": "email"
            },
            {
                "title": "Teléfono",
                "data": "phone"
            },
            {
                "title": "Empresa",
                "data": "company.name"
            },
            {
                "title": "w",
                "data": {
                    id: "id",
                    email: "email"
                },
                "render": function ( data, type, row, meta ) {

                    if(row.loading){

                        return `
                            <div class="d-flex justify-content-center">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </div>
                        ` 

                    }

                    if(row.database){
                        return `
                            <div class="d-flex justify-content-center" style="cursor:pointer" >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </div>
                        ` 
                    }

                    return `
                        <div class="d-flex justify-content-center" style="cursor:pointer" >
                            <i class="fa-solid fa-floppy-disk"></i>
                        </div>
                    `

                }
            } 
        ]
    })

    $('#table_users').on('click', 'td', async function () {

    
        const table = $('#table_users').DataTable()

        const columnIndex = table.column( this ).index()
        const rowIndex = table.row( this ).index()

        if(columnIndex == 4){

            const data =  dataTableData[ rowIndex ]

            
            if(data.database){

                $("#user_id").val(rowIndex)
                $("#user_img").val(data.img)
                $("#user_birthdate").val(data.birthdate)

                $("#modal-title").text(data.name)

                $("#modal").modal({
                    backdrop: 'static',
                    keyboard: false
                },'show')

                // data.update().then( res => {
                //     console.log('OK')
                //     console.log(res)
                // }).catch( err => {
                //     console.log('err')
                //     console.log(err)
                // })

            }else{

                if(data.loading){
                    return 
                }

                data.loading = true
                $('#table_users').dataTable().fnUpdate(data,rowIndex,undefined,false)

                data.save().then( res => {
                    $('#table_users').dataTable().fnUpdate(data,rowIndex,undefined,false)
                }).catch( err => {
                    console.log(err)
                    $('#table_users').dataTable().fnUpdate(data,rowIndex,undefined,false)
                })

            }

        }


        //console.log(table.cell( this ).row().data())
        // var data = table.cell( this ).data();
        // console.log(data);
    })


    $('#user_save').click( function() {

        const index = $("#user_id").val()

        const user = dataTableData[index]

        user.update({
            img: $("#user_img").val(),
            birthdate: $("#user_birthdate").val()
        }).then( res => {
            console.log(res)
        }).catch( err => {
            console.log(err)
        })

        $("#modal").modal('hide')


    })

    $('#user_close').click( function() {

        $("#user_id").val('')
        $("#user_img").val('')
        $("#user_birthdate").val('')

        $("#modal").modal('hide')

    })

 

})

   

</script>