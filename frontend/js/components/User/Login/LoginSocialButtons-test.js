// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { LoginSocialButtons } from './LoginSocialButtons';
import { features } from '../../../redux/modules/default';

describe('<LoginSocialButtons />', () => {
  const ssoList = [
    {
      name: 'Cap Collectif Oauth2 Provider',
      ssoType: 'oauth2',
      labelColor: '#FFFFFF',
      buttonColor: '#ABABAB',
    },
    {
      name: 'France Connect',
      ssoType: 'franceconnect',
      labelColor: '',
      buttonColor: '',
    },
  ];

  const props = {
    features,
    ssoList: [],
  };
  const propsWithFeatureLoginFacebookActivated = {
    features: {
      ...features,
      login_facebook: true,
    },
    ssoList: [],
  };
  const propsWithFeatureLoginSamlActivated = {
    features: {
      ...features,
      login_saml: true,
    },
    ssoList: [],
  };
  const propsWithFeatureLoginOpenIDActivated = {
    features: {
      ...features,
    },
    ssoList: [
      {
        name: 'Cap Collectif Oauth2 Provider',
        ssoType: 'oauth2',
        labelColor: '#FFFFFF',
        buttonColor: '#ABABAB',
      },
    ],
  };
  const propsWithFeatureLoginFranceConnectActivated = {
    features: {
      ...features,
      login_franceconnect: true,
    },
    ssoList: [
      {
        name: 'France Connect',
        ssoType: 'franceconnect',
        labelColor: '',
        buttonColor: '',
      },
    ],
  };
  const propsWithAllLoginFeaturesLoginActivated = {
    features: {
      ...features,
      login_gplus: true,
      login_facebook: true,
      login_saml: true,
      login_franceconnect: true,
    },
    ssoList,
  };

  const propsWithAllLoginFeaturesLoginActivatedAndORSeparatorDisabled = {
    features: {
      ...features,
      login_gplus: true,
      login_facebook: true,
      login_saml: true,
      login_franceconnect: true,
      sso_by_pass_auth: true,
    },
    ssoList,
  };

  it('renders nothing', () => {
    const wrapper = shallow(<LoginSocialButtons {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders only Facebook button', () => {
    const wrapper = shallow(<LoginSocialButtons {...propsWithFeatureLoginFacebookActivated} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders only SAML button', () => {
    const wrapper = shallow(<LoginSocialButtons {...propsWithFeatureLoginSamlActivated} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders only OpenID button', () => {
    const wrapper = shallow(<LoginSocialButtons {...propsWithFeatureLoginOpenIDActivated} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders only FranceConnect button', () => {
    const wrapper = shallow(
      <LoginSocialButtons {...propsWithFeatureLoginFranceConnectActivated} />,
    );
    expect(wrapper).toMatchSnapshot();
  });

  it('renders all buttons', () => {
    const wrapper = shallow(<LoginSocialButtons {...propsWithAllLoginFeaturesLoginActivated} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders without OR separator', () => {
    const wrapper = shallow(
      <LoginSocialButtons {...propsWithAllLoginFeaturesLoginActivatedAndORSeparatorDisabled} />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});
