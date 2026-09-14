import {
  cilSpeedometer,
  cilLockLocked,
  cilUser,
  cilSettings,
  cilBank,
  cilBriefcase,
  cilClipboard,
  cilLibraryAdd,
  cilCash,
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
                icon: cilBank,

            },
            {
                component: 'CNavItem',
                name: 'Hiring Organization',
                to: '/user/hospital',
                icon: cilBriefcase,
            },

            ],
          },

        ]
      : []),

        {
        component: 'CNavItem',
        name: 'Enrolment',
        to: '/enrolment',
        icon: cilClipboard,
        },
        ...(user?.role === 'admin'
      ? [
           {
                component: 'CNavItem',
                name: 'Placement Request',
                to: '/placement-request',
                icon: cilLibraryAdd,
          },
          {
                component: 'CNavItem',
                name: 'Subscription Plan',
                to: '/subscription-plan',
                icon: cilCash,
          },

        ]
      : []),

        {
        component: 'CNavItem',
        name: 'Setting',
        to: '/setting',
        icon: cilSettings,
        },
  ]
}
