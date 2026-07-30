function confirmDelete(){

    return confirm("Are you sure you want to delete this record?");

}


function previewFile(){

    const file=document.getElementById("file");

    if(file.files.length>0){

        alert("Selected File: " + file.files[0].name);

    }

}


function searchTable(){

    let input=document.getElementById("search");

    let filter=input.value.toUpperCase();

    let table=document.getElementById("dataTable");

    let tr=table.getElementsByTagName("tr");

    for(let i=1;i<tr.length;i++){

        let td=tr[i].getElementsByTagName("td")[0];

        if(td){

            let txt=td.textContent||td.innerText;

            if(txt.toUpperCase().indexOf(filter)>-1){

                tr[i].style.display="";

            }else{

                tr[i].style.display="none";

            }

        }

    }

}

function success(message){

    alert(message);

}

function logout(){

    return confirm("Are you really sure you want to logout?");

}