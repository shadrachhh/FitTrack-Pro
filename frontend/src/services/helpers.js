export const blankExercise = () => ({ id: null, name: '', muscle_group: '', description: '' });
export const blankEntry = () => ({ exercise_id: '', sets: 3, reps: 10, weight: 0 });
export const isAdmin = (user) => (user?.role || 'user') === 'admin';

export const formatDate = (value) => {
    if (!value) {
        return 'No date';
    }

    const parsed = new Date(`${value}T00:00:00`);
    return Number.isNaN(parsed.getTime())
        ? value
        : parsed.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};

export const formatWeight = (value) =>
    `${Number(value || 0).toFixed(Number(value || 0) % 1 === 0 ? 0 : 2)} kg`;

export const calculateWorkoutVolume = (workout) =>
    workout.entries.reduce(
        (sum, entry) => sum + (Number(entry.sets) * Number(entry.reps) * Number(entry.weight || 0)),
        0
    );
