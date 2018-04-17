apple.controller('RegistrationForm', ['$rootScope', '$scope', '$stateParams', '$state', 'userService', 'server', function ($rootScope, $scope, $stateParams, $state, userService, server) {
var dictionary ={
    'he':{
    	'firstname':'שם פרטי',
        'firstnameinarabic':'שם פרטי בערבית',
        'lastname':'שם משפחה',
        'lastnameinarabic':'שם משפחה בערבית',
        'city':'עיר מגורים',
        'netacity':'עיר פעילות נטע@',
        'school':'בית ספר',
        'Neighberhood':'שכונה',
        'grade':'כיתה',
        'learnaboutus':'איך שמעת עלינו?',
        'gender':'מגדר',
        'email':'אימייל',
        'phonenumber':'מספר טלפון',
        'parentsphonenumber':'מספר  טלפון של הורים',
        'birthday':'תאריך לידה',
        'else':'אחר',
        'submit':'סבבה'
    },
    'ar':{
    	'firstname':'first name',
        'firstnameinarabic':'first name ar',
        'lastname':'last name',
        'lastnameinarabic':'last name ar',
        'city':'city',
        'netacity':'neta city',
        'school':'school',
        'Neighberhood':'Neighberhood',
        'grade':'grade',
        'learnaboutus':'how did you learn about us?',
        'gender':'gender',
        'email':'email',
        'phonenumber':'phone number',
        'parentsphonenumber':'parents\' phone num',
        'birthday':'birthday',
        'else':'other',
        'submit':'sababa'
    }
}
	$scope.isArabic = $stateParams["lang"]=='ar';
    $scope.dictionary=dictionary[$stateParams["lang"]];
	$scope.nominee = {
		firstname: '',
		lastname: '',
        genderid:'',
		birthday: '',
		email: '',
		phone: '',
		parentsphone: '',
		idnumber: '',
		schoolid: '',
		classid: '',
		cityid: '',
		netacityid: '',
		hearaboutid: '',
		hearaboutother: '',
		schoolother: '',
        cityother:'',
        RegistrationDate:'',
        Neighberhood:''

	}
	$scope.register = function ()
	{
        var data = {};
        data.nominee=$scope.nominee;
        var birthday =data.nominee.birthday;
		birthday = birthday.split(/([\/\.])+/g);
		console.log(birthday);
		data.nominee.birthday=birthday[4]+"-"+birthday[2]+"-"+birthday[0];
		if(birthday[0]>31||birthday[2]>12||birthday[4]<1900)
        {
            alert("נא להזין תאריך לידה תקין");
            return;
        }
        server.requestPhp(data, "AddNominee").then(function (data) {
            $state.transitionTo('SuccessfulRegistration');
        });

		console.log($scope.nominee);
	};
    $scope.schools = [];
	$scope.GetSchoolsByNetaCityId = function () {
        var data = {};
        data.NetaCityId = $scope.nominee.netacityid;
        server.requestPhp(data, "GetSchoolsByNetaCityId").then(function (data) {
            $scope.schools = data;
        });
    }

    $scope.cities = [];
    $scope.GetCities = function () {
        var data={};
        server.requestPhp(data, "GetCities").then(function (data) {
            $scope.cities = data;
        });
    }
    $scope.GetCities();


    $scope.Netacities = [];
    $scope.GetNetaCities = function () {
        var data={};
        server.requestPhp(data, "GetNetaCities").then(function (data) {
            $scope.Netacities = data;
        });
    }
    $scope.GetNetaCities();

    $scope.Classes = [];
    $scope.GetClasses = function () {
        var data={};
        server.requestPhp(data, "GetClasses").then(function (data) {
            $scope.Classes = data;
        });
    }
    $scope.GetClasses();

    $scope.HearAboutUs = [];
    $scope.GetHearAboutUsOptions = function () {
        var data={};
        server.requestPhp(data, "GetHearAboutUsOptions").then(function (data) {
            $scope.HearAboutUs = data;
        });
    }
    $scope.GetHearAboutUsOptions();

    $scope.genders = [];
    $scope.GetGenders = function () {
        var data={};
        server.requestPhp(data, "GetGenders").then(function (data) {
            $scope.genders = data;
        });
    }
    $scope.GetGenders();



}
]);