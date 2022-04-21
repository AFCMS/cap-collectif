/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { EditProfileTabs } from './EditProfileTabs';
import { features } from '../../../redux/modules/default';

describe('<EditProfileTabs />', () => {
  const propsWithoutParis = {
    features: {
      ...features,
      profiles: true,
      login_paris: false,
    },
  };

  const propsWithParisAndNotProfiles = {
    features: {
      ...features,
      profiles: false,
      login_paris: true,
    },
  };

  const viewer = {
    username: 'user',
    displayName: 'iAmAUser',
    media: {
      url: 'http://monimage.com/image1.jpg',
    },
    show_url: 'http://monprofil/profil',
  };

  it('should render all tabs', () => {
    const wrapper = shallow(<EditProfileTabs viewer={viewer} {...propsWithoutParis} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render all tabs except profile, password and account', () => {
    const wrapper = shallow(<EditProfileTabs viewer={viewer} {...propsWithParisAndNotProfiles} />);
    expect(wrapper).toMatchSnapshot();
  });
});
