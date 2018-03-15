apple.directive('hierarchy', [function () {
	return {
		restrict: 'E',
		templateUrl: './directives/hierarchy/hierarchy.html',
		transclude: true,
		scope: {
			node: '=node',
			children: '=children'
		},
		link: function (scope, elem, attrs) {
			console.log(scope.node[scope.children]);
			console.log(scope.node);
			console.log(scope.children);
			if(scope.node[scope.children].length>0)
			{
				$compile('<hierarchy node="child" children="children" ng-repeat="child in node[children]"></collection>')(scope, function(cloned, scope){
					elem.append(cloned); 
				});
			}
		}
	};
} ]);