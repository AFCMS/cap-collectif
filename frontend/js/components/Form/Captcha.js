// @flow
import * as React from 'react';
import ReCAPTCHA from 'react-google-recaptcha';

type Props = {
  onChange: (response: string) => void,
  style?: Object,
  value: ?string,
};

export class Captcha extends React.PureComponent<Props> {
  captcha: ?Object;

  componentDidUpdate(prevProps: Props) {
    const { value } = this.props;
    if (
      prevProps &&
      prevProps.value !== null &&
      !value &&
      typeof window.grecaptcha !== 'undefined'
    ) {
      window.grecaptcha.reset();
    }
  }

  render() {
    const { onChange, style } = this.props;

    return (
      <ReCAPTCHA
        id="recaptcha"
        ref={c => {
          this.captcha = c;
        }}
        style={{ transform: 'scale(0.85)', transformOrigin: '0 0', ...style }}
        sitekey="6LfKLxsTAAAAANGSsNIlspDarsFFK53b4bKiBYKC"
        onChange={onChange}
      />
    );
  }
}

export default Captcha;
