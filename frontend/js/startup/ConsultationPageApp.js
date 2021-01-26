// @flow
import React, { lazy, Suspense } from 'react';
import { connect } from 'react-redux';
import Providers from './Providers';
import type { OwnProps as ConsultationPropositionBoxProps } from '~/components/Consultation/ConsultationPropositionBox';
import Loader from '~ui/FeedbacksIndicators/Loader';
import type { GlobalState } from '~/types';

type ReduxProps = {|
  +hasNewConsultationPage: boolean,
|};

type Props = {|
  ...ReduxProps,
  ...ConsultationPropositionBoxProps,
|};

const ConsultationPropositionBox = lazy(() =>
  import('~/components/Consultation/ConsultationPropositionBox'),
);

// TODO: To use when working on the new consultation index page. For now, only the OpinionPage is being done
// const NewConsultationPage = lazy(() => import('~/components/Consultation/New/ConsultationPage'));

const mapStateToProps = (state: GlobalState) => ({
  hasNewConsultationPage: state.default.features.unstable__new_consultation_page,
});

const ConsultationPageApp = ({ hasNewConsultationPage, ...props }: Props) => {
  return hasNewConsultationPage ? (
    <ConsultationPropositionBox {...props} />
  ) : (
    <ConsultationPropositionBox {...props} />
  );
};

const ConsultationPageAppContainer = connect(mapStateToProps)(ConsultationPageApp);

export default (props: Props) => (
  <Suspense fallback={<Loader />}>
    <Providers>
      <ConsultationPageAppContainer {...props} />
    </Providers>
  </Suspense>
);
