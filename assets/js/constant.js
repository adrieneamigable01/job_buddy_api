

var session = localStorage.getItem('session')!= null ? JSON.parse(atob(localStorage.getItem('session'))) : null;
companyName = 'Don`s Coffe Shop'
if(session != null){
    fullName    = session.fullName;
    firstName   = session.firstName;
    lastName    = session.lastName;
    userName    = session.userName;
    userid   = session.userid;
    email       = session.email;
    userType    = session.userType;
    dateCreated = session.dateCreated;
}
   