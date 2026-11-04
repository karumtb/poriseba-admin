import {
  cilSpeedometer,
  cilUser,
  cilSettings,
  cilBank,
  cilBriefcase,
  cilClipboard,
  cilLibraryAdd,
  cilCash,
  cilMedicalCross,
  cilHome,
} from '@coreui/icons';

export default function getNav(user) {
  return [
    {
      component: 'CNavItem',
      name: 'Dashboard',
      to: '/dashboard',
      icon: cilSpeedometer,
    },

    // Patient Management
    {
      component: 'CNavItem',
      name: 'Patient Management',
      icon: cilMedicalCross,
      items: [
        {
          component: 'CNavItem',
          name: 'Patient Onboarding',
          to: '/patient/onboarding',
          icon: cilBank,
        },
      ],
    },
    {
      component: 'CNavItem',
      name: 'Caregiver Assignment',
      icon: cilHome,
      items: [
        {
          component: 'CNavItem',
          name: 'New Assignment',
          to: '/caregiver/assignment',
          icon: cilBank,
        },
      ],
    },

    ...(user?.role === 'admin'
      ? [
          {
            component: 'CNavGroup',
            name: 'User',
            icon: cilUser,
            items: [
              {
                component: 'CNavItem',
                name: 'Institute',
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
  ];
}
