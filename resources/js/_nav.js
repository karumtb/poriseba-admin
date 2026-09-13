import {
  cilSpeedometer,
  cilLockLocked,
  cilUser,
} from '@coreui/icons'

export default function getNav(user) {
  return [
    {
      component: 'CNavItem',
      name: 'Dashboard',
      to: '/dashboard',
      icon: cilSpeedometer,
    },

    ...(user?.role === 'admin'
      ? [
          {
            component: 'CNavItem',
            name: 'User',
            to: '/user',
            icon: cilUser,
            items: [
            {
                component: 'CNavItem',
                name: 'Instititute',
                to: '/user/institute',

            },
            {
                component: 'CNavItem',
                name: 'Hiring Organization',
                to: '/user/hospital',
            },

            ],
          },

        ]
      : []),

        {
        component: 'CNavItem',
        name: 'Enrolment',
        to: '/enrolment',
        icon: cilUser,
        },
        ...(user?.role === 'admin'
      ? [
           {
                component: 'CNavItem',
                name: 'Placement Request',
                to: '/placement-request',
                icon: cilUser,
          },
          {
                component: 'CNavItem',
                name: 'Subscription Plan',
                to: '/subscription-plan',
                icon: cilUser,
          },

        ]
      : []),

        {
        component: 'CNavItem',
        name: 'Setting',
        to: '/setting',
        icon: cilLockLocked,
        },
  ]
}
